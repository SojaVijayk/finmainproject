<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\PMS\Invoice;
use App\Models\PMS\InvoicePayment;
use App\Models\PMS\Project;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use Carbon\Carbon;
use DB;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        // Get invoices with their projects
        $invoices = Invoice::with(['project', 'requestedBy', 'payments'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->groupBy('status');

        // Get overdue invoices with projects
        $overdueInvoices = Invoice::where('status', Invoice::STATUS_SENT)
            ->where('due_date', '<', today())
            ->with(['project', 'requestedBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Get recent payments with invoice and project
        $recentPayments = InvoicePayment::with(['invoice.project', 'recordedBy'])
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        // Statistics for dashboard cards
        $stats = [
            'total_revenue' => Invoice::where('status', Invoice::STATUS_PAID)->sum('amount'),
            'outstanding' => Invoice::whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])->sum('amount'),
            'overdue' => Invoice::where('status', Invoice::STATUS_OVERDUE)->sum('amount'),
            'draft_invoices' => Invoice::where('status', Invoice::STATUS_DRAFT)->count(),
        ];

        // Data for charts
        $chartData = $this->getChartData();

        return view('pms.finance.dashboard', compact(
            'invoices',
            'overdueInvoices',
            'recentPayments',
            'stats',
            'chartData',
            'pageConfigs'
        ));
    }

    protected function getChartData()
    {
        // Monthly revenue data for the past 12 months
        $revenueData = InvoicePayment::selectRaw('
                YEAR(payment_date) as year,
                MONTH(payment_date) as month,
                SUM(amount) as total
            ')
            ->where('payment_date', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Invoice status distribution
        $statusData = Invoice::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return [
            'revenue' => $revenueData,
            'status' => $statusData
        ];
    }

    public function invoiceIndex_old()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        // Group invoices by project with their invoices
        $projects = Project::with(['invoices' => function($query) {
            $query->orderBy('invoice_date', 'desc');
        }])
        ->whereHas('invoices')
        ->orderBy('title')
        ->get();

        return view('pms.finance.invoices.index', compact('projects', 'pageConfigs'));
    }
     public function invoiceIndex(Request $request)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
           // Get filter values
        $projectId = $request->get('project_id');
        $clientId = $request->get('client_id');
        $investigatorId = $request->get('investigator_id');
        $requestedBy = $request->get('requested_by');
        $invoiceDateFrom = $request->get('invoice_date_from');
        $invoiceDateTo = $request->get('invoice_date_to');
        $status = $request->get('status');
        $invoiceNumber = $request->get('invoice_number'); // ✅ NEW filter

        // Base query for projects with invoices
        $projectsQuery = Project::with([
            'invoices' => function($query) use ($requestedBy, $invoiceDateFrom, $invoiceDateTo, $status,$invoiceNumber) {
                if ($requestedBy) {
                    $query->where('requested_by', $requestedBy);
                }
                if ($invoiceDateFrom) {
                    $query->whereDate('invoice_date', '>=', $invoiceDateFrom);
                }
                if ($invoiceDateTo) {
                    $query->whereDate('invoice_date', '<=', $invoiceDateTo);
                }
                if ($status) {
                    $query->where('status', $status);
                }
                  if ($invoiceNumber) { // ✅ Filter invoices by invoice_number (partial match)
                $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
            }
            },
            'requirement.client',
            'investigator',
            'invoices.requestedBy'
        ])->whereHas('invoices');

        // Apply project filters
        if ($projectId) {
            $projectsQuery->where('id', $projectId);
        }

        // Filter by client through requirement
        if ($clientId) {
            $projectsQuery->whereHas('requirement', function($query) use ($clientId) {
                $query->where('client_id', $clientId);
            });
        }

        // Filter by project investigator
        if ($investigatorId) {
            $projectsQuery->where('project_investigator_id', $investigatorId);
        }

        // Get filtered projects
        $filteredProjects = $projectsQuery->get();

        // Get all data for filter dropdowns
        $allProjects = Project::has('invoices')->get();

        // Get clients that have projects with invoices
        $clients = Client::whereHas('requirements', function($query) {
            $query->whereHas('project', function($projectQuery) {
                $projectQuery->whereHas('invoices');
            });
        })->get();

        // Get investigators (users) who have projects with invoices
        $investigators = User::whereHas('investigatorProjects', function($query) {
            $query->whereHas('invoices');
        })->get();

        // Get users who have requested invoices
        $users = User::whereHas('requestedInvoices')->get();

        // Calculate total filtered invoices count
        $filteredInvoices = collect();
        foreach ($filteredProjects as $project) {
            $filteredInvoices = $filteredInvoices->merge($project->invoices);
        }

        return view('pms.finance.invoices.index', compact(
            'filteredProjects',
            'allProjects',
            'clients',
            'investigators',
            'users',
            'filteredInvoices',
            'pageConfigs'
        ));
    }

    public function processDraft(Invoice $invoice)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        if ($invoice->status != Invoice::STATUS_DRAFT && $invoice->status != Invoice::STATUS_SENT) {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be processed.');
        }

        // Load project relationship if not already loaded
        $invoice->load('project');

        return view('pms.finance.invoices.process', compact('invoice', 'pageConfigs'));
    }

    public function updateDraft(Request $request, Invoice $invoice)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        if ($invoice->status != Invoice::STATUS_DRAFT && $invoice->status != Invoice::STATUS_SENT) {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be processed.');
        }

    try {
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,'.$invoice->id,
            'invoice_date' => 'required|date',
               'invoice_type' => 'required',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'amount' => 'required|numeric|min:0.01',
            'total_amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'required|numeric|min:0.00',
            'description' => 'nullable|string',
            'items'                          => 'required|array|min:1',
        'items.*.description'            => 'required|string|max:255',
        'items.*.amount'                 => 'required|numeric|min:0',
        'items.*.tax_percentage'         => 'nullable|numeric|min:0|max:100',
        'items.*.tax_amount'             => 'nullable|numeric|min:0',
        'items.*.total_with_tax'         => 'nullable|numeric|min:0',
        ]);
 } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'validation_error',
            'errors' => $e->errors(),
        ], 422);
    }
// dd($validated);
         // Remove old items and re-add new ones
    $invoice->items()->delete();
     $total_with_tax = 0;
     $total_tax = 0;
     $total_amount = 0;
    foreach ($validated['items'] as $item) {
        $invoice->items()->create($item);
        $total_tax += $item['tax_amount'];
        $total_with_tax += $item['total_with_tax'];
        $total_amount += $item['amount'];
    }

      $invoice->update(['amount' => $total_amount,'tax_amount' => $total_tax,'total_amount'=> $total_with_tax,'invoice_number'=>$validated['invoice_number'],'invoice_date'=>$validated['invoice_date'],'due_date'=>$validated['due_date'],'description'=>$validated['description'],'invoice_type'=>$validated['invoice_type']]);


        // $invoice->update($validated);

        return redirect()->route('pms.finance.invoices.show', $invoice->id)
            ->with('success', 'Draft invoice updated successfully.');
    }

    public function generateInvoice(Invoice $invoice)
    {
        if ($invoice->status != Invoice::STATUS_DRAFT) {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be generated.');
        }

        // Ensure project is loaded
        $invoice->load('project');
        $project = $invoice->project;

        // Generate invoice number based on project
        // $invoiceNumber = 'INV-' . strtoupper(substr($project->project_code, 0, 3)) . '-' . now()->format('Ymd') . '-' . str_pad($project->invoices()->count() + 1, 3, '0', STR_PAD_LEFT);

        $invoice->update([
            // 'invoice_number' => $invoiceNumber,
            'status' => Invoice::STATUS_SENT,
            'generated_by' => auth()->id(),
        ]);

        // TODO: Send invoice to client0

        return redirect()->back()
            ->with('success', 'Invoice generated and sent to client successfully.');
    }

    public function showInvoice(Invoice $invoice)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        // Load all necessary relationships
        $invoice->load(['project', 'milestone', 'payments.recordedBy']);

        return view('pms.finance.invoices.show', compact('invoice', 'pageConfigs'));
    }

    public function recordPayment(Invoice $invoice)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        if (!in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_PAID,Invoice::STATUS_OVERDUE])) {
            return redirect()->back()
                ->with('error', 'Payments can only be recorded for sent or overdue invoices.');
        }

        // Load project information
        $invoice->load('project');

        return view('pms.finance.invoices.payment', compact('invoice', 'pageConfigs'));
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        if (!in_array($invoice->status, [Invoice::STATUS_SENT,Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE])) {
            return redirect()->back()
                ->with('error', 'Payments can only be recorded for sent or overdue invoices.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$invoice->balance_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['recorded_by'] = auth()->id();

        $invoice->payments()->create($validated);

        // Update invoice status if fully paid
        if ($invoice->fresh()->balance_amount <= 0) {
            $invoice->update(['status' => Invoice::STATUS_PAID]);
        } elseif ($invoice->due_date < today()) {
            $invoice->update(['status' => Invoice::STATUS_OVERDUE]);
        }

        return redirect()->route('pms.finance.invoices.show', $invoice->id)
            ->with('success', 'Payment recorded successfully.');
    }

    public function markAsPaid(Invoice $invoice)
    {
        if (!in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])) {
            return redirect()->back()
                ->with('error', 'Only sent or overdue invoices can be marked as paid.');
        }

        // Load project information
        $invoice->load('project');

        $invoice->payments()->create([
            'amount' => $invoice->balance_amount,
            'payment_date' => now(),
            'payment_method' => 'Manual Payment',
            'recorded_by' => auth()->id(),
        ]);

        $invoice->update(['status' => Invoice::STATUS_PAID]);

        return redirect()->back()
            ->with('success', 'Invoice marked as paid successfully.');
    }

 public function convert($id)
{
    $invoice = Invoice::with('items')->findOrFail($id);

    return response()->json([
        'invoiceId'       => $invoice->id,
        'invoiceNumber'   => $invoice->invoice_number,
        'description'     => $invoice->description,
        'amount'          => $invoice->amount,
        'total_amount'    => $invoice->total_amount,
        'tax_amount'      => $invoice->tax_amount,
        'view'            => view('pms.finance.invoices.partials.proforma-view', compact('invoice'))->render(),
        'edit'            => view('pms.finance.invoices.partials.proforma-edit', compact('invoice'))->render(),
        'tax'             => view('pms.finance.invoices.partials.tax-create', compact('invoice'))->render(),
    ]);
}

public function storeConverted(Request $request, $id)
{
    $proforma = Invoice::with('items')->findOrFail($id);

    $validated = $request->validate([
        // 🔹 Tax Invoice Fields
        'invoice_number'         => 'required|string|max:255|unique:invoices,invoice_number',
        'invoice_date'           => 'required|date',
        'due_date'               => 'required|date|after_or_equal:invoice_date',
        'tax_subtotal'           => 'required|numeric|min:0.01',
        'tax_total_tax'          => 'required|numeric|min:0',
        'tax_total_amount'       => 'required|numeric|min:0.01',

        // 🔹 Tax Invoice Items
        'tax_items'                       => 'required|array|min:1',
        'tax_items.*.description'         => 'required|string|max:255',
        'tax_items.*.amount'              => 'required|numeric|min:0',
        'tax_items.*.tax_percentage'      => 'nullable|numeric|min:0',

        // 🔹 Proforma Invoice Items
        'proforma_items'                  => 'required|array|min:1',
        'proforma_items.*.description'    => 'required|string|max:255',
        'proforma_items.*.amount'         => 'required|numeric|min:0',
        'proforma_items.*.tax_percentage' => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($proforma, $validated) {

        // ✅ Create Tax Invoice
        $taxInvoice = new Invoice();
        $taxInvoice->invoice_number = $validated['invoice_number'];
        $taxInvoice->invoice_date   = $validated['invoice_date'];
        $taxInvoice->due_date       = $validated['due_date'];
        $taxInvoice->invoice_type   = 2;
        $taxInvoice->amount         = $validated['tax_subtotal'];
        $taxInvoice->tax_amount     = $validated['tax_total_tax'];
        $taxInvoice->total_amount   = $validated['tax_total_amount'];
        $taxInvoice->proforma_id    = $proforma->id;
        $taxInvoice->project_id     = $proforma->project_id;
        $taxInvoice->requested_by   = auth()->id();
        $taxInvoice->status         = Invoice::STATUS_SENT;
        $taxInvoice->generated_by   = auth()->id();

        // ✅ Store old Proforma summary as JSON
        $taxInvoice->meta = json_encode([
            'proforma_id'     => $proforma->id,
            'proforma_number' => $proforma->invoice_number,
            'proforma_amount' => $proforma->amount,
            'proforma_tax'    => $proforma->tax_amount,
            'proforma_total'  => $proforma->total_amount,
        ]);
        $taxInvoice->save();

        // ✅ Save Tax Invoice Items
        foreach ($validated['tax_items'] as $item) {
            $amount = (float) $item['amount'];
            $tax    = (float) ($item['tax_percentage'] ?? 0);
            $taxInvoice->items()->create([
                'description'     => $item['description'],
                'amount'          => $amount,
                'tax_percentage'  => $tax,
                'total_with_tax'  => $amount + ($amount * $tax / 100),
            ]);
        }

        // ✅ Update Proforma Items
        $proforma->items()->delete();
        foreach ($validated['proforma_items'] as $item) {
            $amount = (float) $item['amount'];
            $tax    = (float) ($item['tax_percentage'] ?? 0);
            $proforma->items()->create([
                'description'     => $item['description'],
                'amount'          => $amount,
                'tax_percentage'  => $tax,
                'total_with_tax'  => $amount + ($amount * $tax / 100),
            ]);
        }

        // ✅ Update Proforma Totals
        $proforma->amount = collect($validated['proforma_items'])->sum(fn($i) => $i['amount']);
        $proforma->tax_amount = collect($validated['proforma_items'])->sum(fn($i) => $i['amount'] * ($i['tax_percentage'] ?? 0) / 100);
        $proforma->total_amount = $proforma->amount + $proforma->tax_amount;
        $proforma->status = $proforma->total_amount > $taxInvoice->total_amount
            ? 'Converted Partially'
            : 'Converted Fully';
        $proforma->save();
    });

    return response()->json([
        'status'  => true,
        'message' => 'Tax Invoice created and Proforma updated successfully.',
    ]);
}
public function getConvertView(Invoice $invoice)
{
  $pageConfigs = ['myLayout' => 'horizontal'];
    // Ensure only proforma invoices (type = 1) can be converted
    if ($invoice->invoice_type != 1) {
        return redirect()
            ->route('pms.finance.invoices.show', $invoice->id)
            ->with('error', 'Only Proforma invoices can be converted.');
    }

    // Optional: prevent conversion of already converted/cancelled invoices
    if (in_array($invoice->status, ['converted', 'partial_converted', Invoice::STATUS_CANCELLED])) {
        return redirect()
            ->route('pms.finance.invoices.show', $invoice->id)
            ->with('error', 'This proforma invoice is already converted or cancelled.');
    }

    // Render the conversion form
    return view('pms.finance.invoices.convert', compact('invoice','pageConfigs'));
}

public function convertProformaToTaxInvoice(Request $request, Invoice $invoice)
{
    if ($invoice->invoice_type != 1) {
        return back()->with('error', 'Only Proforma invoices can be converted.');
    }

    // $request->validate([
    //     'conversion_type' => 'required|in:cancel,full,partial',
    //     'remark'          => 'required_if:conversion_type,cancel|string|nullable',
    //     'invoice_number'  => 'required_if:conversion_type,full,partial|string|max:255',
    //     'invoice_date'    => 'required_if:conversion_type,full,partial|date',
    //     'due_date'        => 'required_if:conversion_type,full,partial|date|after_or_equal:invoice_date',
    // ]);
  $request->validate([
    'conversion_type' => 'required|in:cancel,full,partial,partial_no_proforma',

    // Remark is required only when cancel
    'remark' => 'required_if:conversion_type,cancel|nullable|string',

    // The rest are required when NOT cancel
    'invoice_number' => 'required_unless:conversion_type,cancel|nullable|string|max:255',
    'invoice_date'   => 'required_unless:conversion_type,cancel|nullable|date',
    'due_date'       => 'required_unless:conversion_type,cancel|nullable|date|after_or_equal:invoice_date',
]);
// print_r($request->all());exit;
    try {
        \DB::beginTransaction();

        $conversionType = $request->conversion_type;
        $newTaxInvoice = null;
        $newProforma = null;

        if ($conversionType === 'cancel') {
            $invoice->update([
                'status' => Invoice::STATUS_CANCELLED,
                'cancel_remark' => $request->remark,
                'cancelled_at'=>now(),
                'cancelled_by'=>auth()->id()
            ]);
        } elseif ($conversionType === 'full') {
            $newTaxInvoice = $invoice->replicate();
            $newTaxInvoice->invoice_type = 2;
            $newTaxInvoice->invoice_number = $request->invoice_number;
            $newTaxInvoice->invoice_date = $request->invoice_date;
            $newTaxInvoice->due_date = $request->due_date;
            $newTaxInvoice->status = Invoice::STATUS_DRAFT;
            $newTaxInvoice->proforma_id = $invoice->id;
            $newTaxInvoice->save();

            foreach ($invoice->items as $item) {
                $newTaxInvoice->items()->create($item->toArray());
            }

            $invoice->update([
                'status' => Invoice::STATUS_CONVERTED,
                'invoice_number' => $invoice->invoice_number . '-CONVERTED',
            ]);
        } elseif ($conversionType === 'partial') {
          $old_invoice_number =$invoice->invoice_number;
             $invoice->update([
                'status' => Invoice::STATUS_PARTIAL_CONVERTED,
                'invoice_number' => $invoice->invoice_number . '-CONVERTED',
            ]);

            $newProforma = $invoice->replicate();
            $newProforma->invoice_type = 1;
            // $newProforma->invoice_number = $invoice->invoice_number . '-P1';
            $newProforma->invoice_number = $old_invoice_number;
            $newProforma->status = Invoice::STATUS_DRAFT;
            $newProforma->save();
            foreach ($invoice->items as $item) {
                $newProforma->items()->create($item->toArray());
            }





            $newTaxInvoice = $invoice->replicate();
            $newTaxInvoice->invoice_type = 2;
            $newTaxInvoice->invoice_number = $request->invoice_number;
            $newTaxInvoice->invoice_date = $request->invoice_date;
            $newTaxInvoice->due_date = $request->due_date;
            $newTaxInvoice->status = Invoice::STATUS_DRAFT;
             $newTaxInvoice->proforma_id = $invoice->id;
            $newTaxInvoice->save();
            foreach ($invoice->items as $item) {
                $newTaxInvoice->items()->create($item->toArray());
            }

              \DB::commit();
              return redirect()
            ->route('pms.finance.invoices.show', $newTaxInvoice ? $newTaxInvoice->id : $invoice->id)
            ->with('success', 'Invoice conversion completed successfully.');

            // return redirect()->route('pms.finance.invoices.partial.edit', [
            //     'proforma' => $newProforma->id,
            //     'tax' => $newTaxInvoice->id,
            // ]);
        }
         // PARTIAL CONVERSION (No new Proforma)
        elseif ($conversionType === 'partial_no_proforma') {
            $invoice->update([
                'status' => Invoice::STATUS_PARTIAL_NO_PROFORMA,
                'invoice_number' => $invoice->invoice_number . '-PARTIAL-CONVERTED',
            ]);

            // Create only new Tax Invoice
              $newTaxInvoice = $invoice->replicate();
            $newTaxInvoice->invoice_type = 2;
            $newTaxInvoice->invoice_number = $request->invoice_number;
            $newTaxInvoice->invoice_date = $request->invoice_date;
            $newTaxInvoice->due_date = $request->due_date;
            $newTaxInvoice->status = Invoice::STATUS_DRAFT;
             $newTaxInvoice->proforma_id = $invoice->id;
            $newTaxInvoice->save();
            foreach ($invoice->items as $item) {
                $newTaxInvoice->items()->create($item->toArray());
            }
            // $newTaxInvoice = new Invoice([
            //     'project_id' => $invoice->project_id,
            //     'milestone_id' => $invoice->milestone_id,
            //     'invoice_type' => 2,
            //     'invoice_number' => $request->invoice_number,
            //     'invoice_date' => $request->invoice_date,
            //     'due_date' => $request->due_date,
            //     'status' => Invoice::STATUS_DRAFT,
            //     'requested_by' => $invoice->requested_by,
            //     'generated_by' => $invoice->generated_by,
            //     'description' => 'Created as Partial (No Proforma) conversion from ' . $invoice->invoice_number,
            // ]);
            // $newTaxInvoice->save();

            \DB::commit();

            return redirect()
                ->route('pms.finance.invoices.show', $newTaxInvoice->id)
                ->with('success', 'Partial (No Proforma) Tax Invoice created successfully.');
        }

        \DB::commit();



        return redirect()
            ->route('pms.finance.invoices.show', $newTaxInvoice ? $newTaxInvoice->id : $invoice->id)
            ->with('success', 'Invoice conversion completed successfully.');

    } catch (\Throwable $e) {
        \DB::rollBack();
        print_r($e->getMessage());
        // return back()->with('error', 'Error converting invoice: ' . $e->getMessage());
    }
}
public function editPartial(Request $request)
{
  $pageConfigs = ['myLayout' => 'horizontal'];
    $proformaInvoice = Invoice::findOrFail($request->query('proforma'));
    $taxInvoice = Invoice::findOrFail($request->query('tax'));

    return view('pms.finance.invoices.partials.partial_edit', compact('proformaInvoice', 'taxInvoice','pageConfigs'));
}



}