<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentDespatch;
use App\Models\DespatchType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentDespatchController extends Controller
{

   public function create(Request $request)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $document = Document::findOrFail($request->get('document_id'));
         $user = Auth::user();
             if ($user->hasRole('despatcher')) {
        $types = DespatchType::all();
             }
             else{
               $types = DespatchType::where('id',2)->get();
             }

        return view('documents.despatch.create', compact('document', 'types','pageConfigs'));
    }


    public function store(Request $request, Document $document)
    {
        $request->validate([
            'despatch_date' => 'required|date',
          'type_id' => 'required|exists:despatch_types,id',
            'actual_despatch_date' => 'nullable|date',
            'tracking_number' => 'nullable|string|max:50',
            'mail_id' => 'nullable|email|max:255',
               'courier_name' => 'nullable|string|max:255',
            'send_by' => 'nullable|string|max:255',
            'acknowledgement_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'despatch_receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
    'despatch_date',
    'actual_despatch_date',
    'type_id',
    'mail_id',
    'send_by',
    'tracking_number',
    'courier_name'
]);
        $data['document_id'] = $document->id;
        $data['created_by'] = Auth::id();

        // Handle optional uploads
        if ($request->hasFile('acknowledgement_file')) {
            $data['acknowledgement_file'] = $request->file('acknowledgement_file')
                ->store('acknowledgements', 'public');
        }

        if ($request->hasFile('despatch_receipt')) {
            $data['despatch_receipt'] = $request->file('despatch_receipt')
                ->store('despatch_receipts', 'public');
        }

        DocumentDespatch::create($data);

        return redirect()->back()->with('success', 'Despatch record added successfully!');
    }

    public function uploadAcknowledgement(Request $request, DocumentDespatch $despatch)
    {
        $request->validate([
            'acknowledgement_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('acknowledgement_file')) {
            $path = $request->file('acknowledgement_file')->store('acknowledgements', 'public');
            $despatch->update(['acknowledgement_file' => $path]);
        }

        return redirect()->back()->with('success', 'Acknowledgement uploaded successfully!');
    }

     public function edit(DocumentDespatch $despatch)
    {
        $types = DespatchType::all();
        return view('documents.despatch.edit', compact('despatch', 'types'));
    }

    /**
     * Update existing despatch record.
     */
    public function update(Request $request, DocumentDespatch $despatch)
    {
        $validated = $request->validate([
            'despatch_date' => 'required|date',
            'type_id' => 'required|exists:despatch_types,id',
            'tracking_number' => 'nullable|string|max:255',
            'despatch_receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'acknowledgement_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('despatch_receipt')) {
            if ($despatch->despatch_receipt) {
                Storage::disk('public')->delete($despatch->despatch_receipt);
            }
            $validated['despatch_receipt'] = $request->file('despatch_receipt')
                ->store('despatch_receipts', 'public');
        }

        if ($request->hasFile('acknowledgement_file')) {
            if ($despatch->acknowledgement_file) {
                Storage::disk('public')->delete($despatch->acknowledgement_file);
            }
            $validated['acknowledgement_file'] = $request->file('acknowledgement_file')
                ->store('despatch_ack', 'public');
        }

        $despatch->update($validated);

        return redirect()->route('documents.index')
            ->with('success', 'Despatch updated successfully.');
    }

    /**
     * Delete despatch record (you already have this method).
     */
    public function destroy(DocumentDespatch $despatch)
    {
        if ($despatch->acknowledgement_file) {
            Storage::disk('public')->delete($despatch->acknowledgement_file);
        }
        if ($despatch->despatch_receipt) {
            Storage::disk('public')->delete($despatch->despatch_receipt);
        }
        $despatch->delete();

        return redirect()->back()->with('success', 'Despatch deleted successfully.');
    }


}