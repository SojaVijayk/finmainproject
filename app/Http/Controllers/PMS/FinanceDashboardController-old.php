<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\PMS\Invoice;
use App\Models\PMS\InvoicePayment;
use App\Models\PMS\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    public function index()
    {

        $pageConfigs = ['myLayout' => 'horizontal'];

        // Get invoices grouped by status
        $invoices = Invoice::with(['project', 'requestedBy', 'payments'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->groupBy('status');

        // Get overdue invoices
        $overdueInvoices = Invoice::where('status', Invoice::STATUS_SENT)
            ->where('due_date', '<', today())
            ->with(['project', 'requestedBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Get recent payments
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
        ),['pageConfigs'=> $pageConfigs]);
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

    public function invoiceIndex()
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $pageConfigs = ['myLayout' => 'horizontal'];

        // Group invoices by project
        $projects = Project::with(['invoices' => function($query) {
            $query->orderBy('invoice_date', 'desc');
        }])
        ->whereHas('invoices')
        ->orderBy('title')
        ->get();

        return view('pms.finance.invoices.index', compact('projects', 'pageConfigs'));
    }

    public function processDraft(Invoice $invoice)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        if ($invoice->status != Invoice::STATUS_DRAFT) {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be processed.');
        }

        return view('pms.finance.invoices.process', compact('invoice'),['pageConfigs'=> $pageConfigs]);
    }

    public function updateDraft(Request $request, Invoice $invoice)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        if ($invoice->status != Invoice::STATUS_DRAFT) {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be processed.');
        }

        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,'.$invoice->id,
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return redirect()->route('pms.finance.invoices.show', $invoice->id)
            ->with('success', 'Draft invoice updated successfully.');
    }

    public function generateInvoice(Project $project, Invoice $invoice)
    {
        if ($invoice->status != Invoice::STATUS_DRAFT) {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be generated.');
        }
         // Generate invoice number (you might have your own format)
        $invoiceNumber = 'INV-' . strtoupper(substr($project->project_code, 0, 3)) . '-' . now()->format('Ymd') . '-' . str_pad($project->invoices()->count() + 1, 3, '0', STR_PAD_LEFT);


        $invoice->update([
           'invoice_number' => $invoiceNumber,
            'status' => Invoice::STATUS_SENT,
            'generated_by' => auth()->id(),
        ]);

        // TODO: Send invoice to client

        return redirect()->back()
            ->with('success', 'Invoice generated and sent to client successfully.');
    }

    public function showInvoice(Invoice $invoice)
    {

        $pageConfigs = ['myLayout' => 'horizontal'];
        $invoice->load(['project', 'milestone', 'payments.recordedBy']);

        return view('pms.finance.invoices.show', compact('invoice', 'pageConfigs'));
    }

    public function recordPayment(Invoice $invoice)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        if (!in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])) {
            return redirect()->back()
                ->with('error', 'Payments can only be recorded for sent or overdue invoices.');
        }

        return view('pms.finance.invoices.payment', compact('invoice','pageConfigs'));
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        if (!in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])) {
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
}