<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tapal;
use App\Models\Document;
use App\Models\PMS\Project;
use App\Models\User;
use App\Models\DocumentAttachment;
use App\Models\PMS\ProjectDocument;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PDF; // Assuming a PDF wrapper is available, or we use CSV exports

class AuditController extends Controller
{
  // Hardcoded credentials for internal audit use
  private $allowedUsername = 'audit_user';
  private $allowedPassword = 'Audit@2025'; // Consider moving to .env in production if possible

  public function showLogin()
  {
    return view('audit.login');
  }

  public function processLogin(Request $request)
  {
    $request->validate([
      'username' => 'required',
      'password' => 'required',
    ]);

    if ($request->username === $this->allowedUsername && $request->password === $this->allowedPassword) {
      session(['audit_logged_in' => true]);
      return redirect()->route('audit.dashboard');
    }

    return back()->withErrors(['username' => 'Invalid credentials']);
  }

  public function logout()
  {
    session()->forget('audit_logged_in');
    return redirect()->route('audit.login');
  }

  public function dashboard()
  {
    return view('audit.dashboard');
  }

  // ================= TAPALS =================

  public function tapalIndex(Request $request)
  {
    $query = Tapal::with(['creator', 'currentHolder', 'movements']);

    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where(function ($q) use ($search) {
        $q->where('tapal_number', 'like', "%$search%")
          ->orWhere('subject', 'like', "%$search%")
          ->orWhere('ref_number', 'like', "%{$search}%")
          ->orWhere('from_name', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    if ($request->filled('from_date')) {
      $query->whereDate('created_at', '>=', $request->from_date);
    }
    if ($request->filled('to_date')) {
      $query->whereDate('created_at', '<=', $request->to_date);
    }

    $tapals = $query->latest()->paginate(25);
    $pageConfigs = ['myLayout' => 'blank']; // Using blank layout for custom audit view

    return view('audit.tapals.index', compact('tapals', 'pageConfigs'));
  }

  public function tapalShow($id)
  {
    $tapal = Tapal::with([
      'creator',
      'currentHolder',
      'movements.fromUser',
      'movements.toUser',
      'attachments',
    ])->findOrFail($id);
    $pageConfigs = ['myLayout' => 'blank'];
    return view('audit.tapals.show', compact('tapal', 'pageConfigs'));
  }

  public function exportTapals(Request $request)
  {
    // Simple CSV Export Logic
    $query = Tapal::query();
    if ($request->filled('from_date')) {
      $query->whereDate('created_at', '>=', $request->from_date);
    }
    if ($request->filled('to_date')) {
      $query->whereDate('created_at', '<=', $request->to_date);
    }
    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where(function ($q) use ($search) {
        $q->where('tapal_number', 'like', "%$search%")->orWhere('subject', 'like', "%$search%");
      });
    }

    $tapals = $query->get();
    $csvFileName = 'tapals_audit_export_' . date('Y-m-d') . '.csv';
    $headers = [
      'Content-type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=$csvFileName",
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
    ];

    $callback = function () use ($tapals) {
      $file = fopen('php://output', 'w');
      fputcsv($file, ['ID', 'Tapal Number', 'Subject', 'From', 'Created Date', 'Status']);

      foreach ($tapals as $tapal) {
        fputcsv($file, [
          $tapal->id,
          $tapal->tapal_number,
          $tapal->subject,
          $tapal->from_name,
          $tapal->created_at->format('Y-m-d'),
          $tapal->status ?? 'N/A',
        ]);
      }
      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }

  // ================= DOCUMENTS =================

  public function documentIndex(Request $request)
  {
    $query = Document::with(['documentType', 'creator', 'code']);

    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where(function ($q) use ($search) {
        $q->where('document_number', 'like', "%$search%")->orWhere('subject', 'like', "%$search%");
      });
    }
    if ($request->filled('year')) {
      $query->where('year', $request->year);
    }

    $documents = $query->latest()->paginate(25);
    $pageConfigs = ['myLayout' => 'blank'];
    return view('audit.documents.index', compact('documents', 'pageConfigs'));
  }

  public function documentShow($id)
  {
    $document = Document::with([
      'documentType',
      'creator',
      'authorizedPerson',
      'code.user',
      'attachments',
      'histories.user',
      'despatches.type',
      'despatches.creator',
    ])->findOrFail($id);
    $pageConfigs = ['myLayout' => 'blank'];
    return view('audit.documents.show', compact('document', 'pageConfigs'));
  }

  public function downloadDocumentAttachment(DocumentAttachment $attachment)
  {
    if (!session('audit_logged_in')) {
      return redirect()->route('audit.login');
    }
    if (!Storage::disk('public')->exists($attachment->file_path)) {
      // Try fallback if path stored differently
      if (Storage::disk('public')->exists('documents/' . $attachment->file_path)) {
        return Storage::disk('public')->download('documents/' . $attachment->file_path, $attachment->original_name);
      }
      // Try absolute path check just in case or error
      abort(404, 'File not found');
    }
    return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
  }

  public function exportDocuments(Request $request)
  {
    $query = Document::query();
    if ($request->filled('year')) {
      $query->where('year', $request->year);
    }
    $documents = $query->get();
    $csvFileName = 'documents_audit_export_' . date('Y-m-d') . '.csv';
    $headers = [
      'Content-type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=$csvFileName",
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
    ];
    $callback = function () use ($documents) {
      $file = fopen('php://output', 'w');
      fputcsv($file, ['Document No', 'Subject', 'Type', 'Created By', 'Created At']);

      foreach ($documents as $doc) {
        fputcsv($file, [
          $doc->document_number,
          $doc->subject,
          $doc->documentType->name ?? '',
          $doc->creator->name ?? '',
          $doc->created_at->format('Y-m-d'),
        ]);
      }
      fclose($file);
    };
    return response()->stream($callback, 200, $headers);
  }

  // ================= PROJECTS =================

  public function projectIndex(Request $request)
  {
    $query = Project::with(['requirement', 'investigator']);

    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where('title', 'like', "%$search%")->orWhere('project_code', 'like', "%$search%");
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('investigator_id')) {
      $query->where('project_investigator_id', $request->investigator_id);
    }

    $projects = $query->latest()->paginate(25);
    $investigators = User::whereHas('roles', function ($q) {
      $q->where('name', 'faculty');
    })->get(); // Assuming faculty are investigators
    $pageConfigs = ['myLayout' => 'blank'];

    return view('audit.projects.index', compact('projects', 'investigators', 'pageConfigs'));
  }

  public function projectShow($id)
  {
    $project = Project::with([
      'requirement',
      'proposal',
      'investigator',
      'teamMembers.user',
      'milestones.tasks',
      'documents',
      'invoices',
      'expenseComponents',
      'expenses',
    ])->findOrFail($id);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('audit.projects.show', compact('project', 'pageConfigs'));
  }

  public function downloadProjectDocument($projectId, $documentId)
  {
    if (!session('audit_logged_in')) {
      return redirect()->route('audit.login');
    }
    $document = ProjectDocument::where('project_id', $projectId)->findOrFail($documentId);

    if (!Storage::disk('public')->exists($document->file_path)) {
      abort(404, 'File not found');
    }
    return Storage::disk('public')->download($document->file_path, $document->file_name);
  }

  public function exportProjects(Request $request)
  {
    $projects = Project::with('investigator')->get();
    $csvFileName = 'projects_audit_export_' . date('Y-m-d') . '.csv';
    $headers = [
      'Content-type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=$csvFileName",
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
    ];

    $callback = function () use ($projects) {
      $file = fopen('php://output', 'w');
      fputcsv($file, ['Project Code', 'Title', 'Investigator', 'Budget', 'Status']);

      foreach ($projects as $p) {
        fputcsv($file, [
          $p->project_code,
          $p->title,
          $p->investigator->name ?? 'N/A',
          $p->budget,
          $p->status == 1 ? 'Ongoing' : ($p->status == 2 ? 'Completed' : 'Initiated'),
        ]);
      }
      fclose($file);
    };
    return response()->stream($callback, 200, $headers);
  }
}
