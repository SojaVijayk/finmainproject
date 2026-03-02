<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PMS\RequirementStoreRequest;
use App\Http\Requests\PMS\RequirementUpdateRequest;
use App\Models\PMS\Requirement;
use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;
use App\Models\Client;
use App\Models\ClientContactPerson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequirementController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $requirements = Requirement::with(['category', 'subcategory', 'client', 'contactPerson'])
      ->where('proposal_status', 0)
      // ->where('created_by', Auth::id())
      // ->orWhere('allocated_to', Auth::id())
      ->where(function ($q) {
        $q->where('created_by', Auth::id())->orWhere('allocated_to', Auth::id());
      })
      ->get();

    // ->latest()
    // ->paginate(20);

    return view('pms.requirements.index', compact('requirements'), ['pageConfigs' => $pageConfigs]);
  }
  public function proposalsList()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $requirements = Requirement::with(['category', 'subcategory', 'client', 'contactPerson'])

      ->whereIn('status', [2, 5])
      ->where(function ($query) {
        $query->where('created_by', Auth::id())->orWhere('allocated_to', Auth::id());
      })
      ->doesntHave('proposals')
      ->latest()
      ->paginate(20);

    return view('pms.requirements.index', compact('requirements'), ['pageConfigs' => $pageConfigs]);
  }

  public function masterList()
  {
    $user = Auth::user();
    if (!$user->hasRole(['director', 'finance'])) {
      return redirect()
        ->back()
        ->with('error', 'Unauthorized access.');
    }

    $pageConfigs = ['myLayout' => 'horizontal'];
    // Fetch ALL requirements for Director/Finance
    $requirements = Requirement::with([
      'category',
      'subcategory',
      'client',
      'contactPerson',
      'proposals.projects',
      'project',
    ])
      ->latest()
      ->get();

    $isMasterView = true;

    return view('pms.requirements.index', compact('requirements', 'isMasterView'), ['pageConfigs' => $pageConfigs]);
  }

  public function create()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $categories = ProjectCategory::all();
    $clients = Client::all();
    $types = [
      Requirement::TYPE_REQUIREMENT => 'Requirement',
      Requirement::TYPE_DIRECT_PROPOSAL => 'Direct Proposal',
      Requirement::TYPE_TENDER => 'Tender',
    ];

    return view('pms.requirements.create', compact('categories', 'clients', 'types'), ['pageConfigs' => $pageConfigs]);
  }

  public function store(RequirementStoreRequest $request)
  {
    $data = $request->validated();
    // $data['temp_no'] = 'REQ-' . Str::upper(Str::random(8));
    $data['temp_no'] = Requirement::generateRequirementCode($request->client_id, $request->project_category_id);
    $data['created_by'] = Auth::id();
    $data['status'] = Requirement::STATUS_INITIATED;

    $requirement = Requirement::create($data);

    // Handle document uploads if any
    if ($request->hasFile('documents')) {
      foreach ($request->file('documents') as $file) {
        $path = $file->store('public/requirements/documents');

        $requirement->documents()->create([
          'name' => $file->getClientOriginalName(),
          'path' => $path,
          'type' => $file->getClientMimeType(),
          'size' => $file->getSize(),
          'uploaded_by' => Auth::id(),
        ]);
      }
    }

    if ($request->filled('selected_files')) {
      foreach ($request->selected_files as $filePath) {
        // You can either "link" the file or "copy" it to requirements folder.
        // Option 1 (recommended): Just link it
        $requirement->documents()->create([
          'name' => basename($filePath),
          'path' => $filePath,
          'type' => mime_content_type(storage_path('app/' . $filePath)),
          'size' => filesize(storage_path('app/' . $filePath)),
          'uploaded_by' => Auth::id(),
          'source' => 'linked', // mark as linked instead of uploaded
        ]);

        // Option 2 (if you prefer to make a copy):

        // $newPath = 'public/requirements/documents/' . basename($filePath);
        // Storage::copy($filePath, $newPath);
        // $requirement->documents()->create([
        //     'name' => basename($filePath),
        //     'path' => $newPath,
        //     'type' => mime_content_type(storage_path('app/' . $filePath)),
        //     'size' => filesize(storage_path('app/' . $filePath)),
        //     'uploaded_by' => Auth::id(),
        //     'source' => 'copied',
        // ]);
      }
    }

    return redirect()
      ->route('pms.requirements.show', $requirement->id)
      ->with('success', 'Requirement created successfully.');
  }

  public function show(Requirement $requirement)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $requirement->load([
      'category',
      'subcategory',
      'client',
      'contactPerson',
      'allocatedTo',
      'allocatedBy',
      'creator',
      'documents',
      'proposals',
    ]);
    $users = User::with('employee')
      ->whereHas('employee', function ($q) {
        $q->whereIn('designation', [2, 7, 9]);
      })
      ->get();

    return view('pms.requirements.show', compact('requirement', 'users'), ['pageConfigs' => $pageConfigs]);
  }

  public function edit(Requirement $requirement)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    if ($requirement->status != Requirement::STATUS_INITIATED) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be edited in its current status.');
    }

    $categories = ProjectCategory::all();
    $subcategories = ProjectSubcategory::where('category_id', $requirement->project_category_id)->get();
    $clients = Client::all();
    $contacts = ClientContactPerson::where('client_id', $requirement->client_id)->get();
    $types = [
      Requirement::TYPE_REQUIREMENT => 'Requirement',
      Requirement::TYPE_DIRECT_PROPOSAL => 'Direct Proposal',
      Requirement::TYPE_TENDER => 'Tender',
    ];

    return view(
      'pms.requirements.edit',
      compact('requirement', 'categories', 'subcategories', 'clients', 'contacts', 'types'),
      ['pageConfigs' => $pageConfigs]
    );
  }

  public function update(RequirementUpdateRequest $request, Requirement $requirement)
  {
    if ($requirement->status != Requirement::STATUS_INITIATED) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be edited in its current status.');
    }

    $data = $request->validated();
    $requirement->update($data);

    // Handle document uploads if any
    if ($request->hasFile('documents')) {
      foreach ($request->file('documents') as $file) {
        $path = $file->store('public/requirements/documents');

        $requirement->documents()->create([
          'name' => $file->getClientOriginalName(),
          'path' => $path,
          'type' => $file->getClientMimeType(),
          'size' => $file->getSize(),
          'uploaded_by' => Auth::id(),
        ]);
      }
    }

    return redirect()
      ->route('pms.requirements.show', $requirement->id)
      ->with('success', 'Requirement updated successfully.');
  }

  public function destroy(Requirement $requirement)
  {
    if ($requirement->status != Requirement::STATUS_INITIATED) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be deleted in its current status.');
    }

    $requirement->delete();

    return redirect()
      ->route('pms.requirements.index')
      ->with('success', 'Requirement deleted successfully.');
  }

  public function submitForApproval(Requirement $requirement)
  {
    if ($requirement->status != Requirement::STATUS_INITIATED) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be submitted for approval in its current status.');
    }

    $requirement->update([
      'status' =>
        $requirement->type_id == Requirement::TYPE_DIRECT_PROPOSAL
          ? Requirement::STATUS_SENT_TO_DIRECTOR
          : Requirement::STATUS_SENT_TO_DIRECTOR,
    ]);

    // TODO: Notify director or PAC based on type

    return redirect()
      ->back()
      ->with('success', 'Requirement submitted for approval successfully.');
  }

  public function approve(Requirement $requirement)
  {
    if (!in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR])) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be approved in its current status.');
    }

    $requirement->update([
      'status' => Requirement::STATUS_APPROVED_BY_DIRECTOR,
      'allocated_by' => Auth::id(),
      'allocated_at' => now(),
      'allocated_to' => $requirement->created_by,
    ]);

    // TODO: Notify allocated user and creator

    // return redirect()->back()
    //     ->with('success', 'Requirement approved successfully.');
    return redirect()->route('pms.dashboard');
  }

  public function reject(Requirement $requirement)
  {
    if (!in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR, Requirement::STATUS_SENT_TO_PAC])) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be rejected in its current status.');
    }

    $requirement->update([
      'status' => Requirement::STATUS_REJECTED,
    ]);

    // TODO: Notify creator

    return redirect()
      ->back()
      ->with('success', 'Requirement rejected successfully.');
  }

  public function sentToPac(Requirement $requirement)
  {
    if (!in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR])) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be approved in its current status.');
    }

    $requirement->update([
      'status' => Requirement::STATUS_SENT_TO_PAC,
      // 'allocated_by' => Auth::id(),
      // 'allocated_at' => now(),
    ]);

    // TODO: Notify allocated user and creator

    return redirect()
      ->back()
      ->with('success', 'Requirement sent to PAC successfully.');
  }

  public function allocate(Requirement $requirement, Request $request)
  {
    $request->validate([
      'allocated_to' => 'required|exists:users,id',
    ]);

    if (!in_array($requirement->status, [Requirement::STATUS_SENT_TO_PAC, Requirement::STATUS_APPROVED_BY_DIRECTOR])) {
      return redirect()
        ->back()
        ->with('error', 'Requirement cannot be allocated in its current status.');
    }

    $requirement->update([
      'status' => Requirement::STATUS_APPROVED_BY_PAC,
      'allocated_to' => $request->allocated_to,
      'allocated_by' => Auth::id(),
      'allocated_at' => now(),
    ]);

    // TODO: Notify allocated user

    return redirect()
      ->back()
      ->with('success', 'Requirement allocated successfully.');
  }
  public function getSubcategories(ProjectCategory $category)
  {
    $subcategories = $category->subCategories;
    return response()->json($subcategories);
  }

  public function getClientContacts(Client $client)
  {
    $contacts = $client->contactPersons;
    return response()->json($contacts);
  }

  public function process(Request $request)
  {
    $action = $request->input('action');
    $type = $request->input('type');
    $ids = $request->input('ids', []);

    if (empty($ids)) {
      return response()->json([
        'success' => false,
        'message' => 'No items selected.',
      ]);
    }

    try {
      switch ($type) {
        case 'requirements':
          $result = $this->processRequirements($action, $ids);
          break;
        // case 'proposals':
        //     $result = $this->processProposals($action, $ids);
        //     break;
        default:
          return response()->json([
            'success' => false,
            'message' => 'Invalid type specified.',
          ]);
      }

      return response()->json($result);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
      ]);
    }
  }

  private function processRequirements($action, $ids)
  {
    $requirements = Requirement::whereIn('id', $ids)->get();
    $processed = 0;
    $errors = [];

    foreach ($requirements as $requirement) {
      try {
        switch ($action) {
          case 'approve':
            if (!in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR])) {
              $errors[] = "Requirement {$requirement->temp_no} cannot be approved in its current status.";
              continue 2;
            }

            $requirement->update([
              'status' => Requirement::STATUS_APPROVED_BY_DIRECTOR,
              'allocated_by' => Auth::id(),
              'allocated_at' => now(),
              'allocated_to' => $requirement->created_by,
            ]);
            break;

          case 'send_to_pac':
            if (!in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR])) {
              $errors[] = "Requirement {$requirement->temp_no} cannot be sent to PAC in its current status.";
              continue 2;
            }

            $requirement->update([
              'status' => Requirement::STATUS_SENT_TO_PAC,
            ]);
            break;

          case 'reject':
            if (
              !in_array($requirement->status, [Requirement::STATUS_SENT_TO_DIRECTOR, Requirement::STATUS_SENT_TO_PAC])
            ) {
              $errors[] = "Requirement {$requirement->temp_no} cannot be rejected in its current status.";
              continue 2;
            }

            $requirement->update([
              'status' => Requirement::STATUS_REJECTED,
            ]);
            break;

          default:
            $errors[] = "Invalid action for requirement {$requirement->temp_no}.";
            continue 2;
        }

        $processed++;
      } catch (\Exception $e) {
        $errors[] = "Error processing requirement {$requirement->temp_no}: " . $e->getMessage();
      }
    }

    return [
      'success' => true,
      'message' =>
        "Successfully processed {$processed} requirements. " . ($errors ? 'Errors: ' . implode(', ', $errors) : ''),
    ];
  }
}
