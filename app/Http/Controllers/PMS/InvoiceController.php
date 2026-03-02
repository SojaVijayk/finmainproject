<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PMS\InvoiceStoreRequest;
use App\Http\Requests\PMS\InvoiceUpdateRequest;
use App\Models\PMS\Invoice;
use App\Models\PMS\InvoiceItem;
use App\Models\PMS\InvoicePayment;
use App\Models\PMS\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Imports\InvoiceImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
  public function index(Project $project)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $invoices = $project
      ->invoices()
      ->with(['payments', 'requestedBy', 'generatedBy'])
      ->latest()
      ->get();

    return view('pms.invoices.index', compact('project', 'invoices'), ['pageConfigs' => $pageConfigs]);
  }

  public function create(Project $project)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    if ($project->status == Project::STATUS_ARCHIVED) {
      return redirect()
        ->back()
        ->with('error', 'Cannot create invoices for a completed project.');
    }

    $milestones = $project
      ->milestones()
      ->where('invoice_trigger', true)
      ->whereDoesntHave('invoice')
      ->get();

    return view('pms.invoices.create', compact('project', 'milestones'), ['pageConfigs' => $pageConfigs]);
  }

  public function store(InvoiceStoreRequest $request, Project $project)
  {
    if ($project->status == Project::STATUS_ARCHIVED) {
      return redirect()
        ->back()
        ->with('error', 'Cannot create invoices for a completed project.');
    }

    $data = $request->validated();
    $data['project_id'] = $project->id;
    $data['requested_by'] = Auth::id();
    $data['status'] = Invoice::STATUS_DRAFT;

    $items = $request->input('items', []);

    $subtotal = 0;
    $totalTax = 0;
    $grandTotal = 0;

    foreach ($items as $item) {
      $amount = (float) $item['amount'];
      $taxPercentage = (float) ($item['tax_percentage'] ?? 0);
      $taxAmount = ($amount * $taxPercentage) / 100;
      $totalWithTax = $amount + $taxAmount;

      $subtotal += $amount;
      $totalTax += $taxAmount;
      $grandTotal += $totalWithTax;
    }

    $data['amount'] = $subtotal;
    $data['tax_amount'] = $totalTax;
    $data['total_amount'] = $grandTotal;

    $invoice = Invoice::create($data);
    foreach ($items as $item) {
      $amount = (float) $item['amount'];
      $taxPercentage = (float) ($item['tax_percentage'] ?? 0);
      $taxAmount = ($amount * $taxPercentage) / 100;
      $totalWithTax = $amount + $taxAmount;

      $invoice->items()->create([
        'description' => $item['description'],
        'amount' => $amount,
        'tax_percentage' => $taxPercentage,
        'tax_amount' => $taxAmount,
        'total_with_tax' => $totalWithTax,
      ]);
    }

    return redirect()
      ->route('pms.invoices.show', [$project->id, $invoice->id])
      ->with('success', 'Invoice created successfully.');
  }

  public function show(Project $project, Invoice $invoice)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $invoice->load(['payments.recordedBy', 'milestone', 'project']);

    return view('pms.invoices.show', compact('project', 'invoice'), ['pageConfigs' => $pageConfigs]);
  }

  public function edit(Project $project, Invoice $invoice)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    if ($invoice->status != Invoice::STATUS_DRAFT) {
      return redirect()
        ->back()
        ->with('error', 'Only draft invoices can be edited.');
    }
    $milestones = $project
      ->milestones()
      ->where('invoice_trigger', true)
      ->whereDoesntHave('invoice')
      ->get();

    return view('pms.invoices.edit', compact('project', 'invoice', 'milestones'), ['pageConfigs' => $pageConfigs]);
  }

  public function update(InvoiceUpdateRequest $request, Project $project, Invoice $invoice)
  {
    if ($invoice->status != Invoice::STATUS_DRAFT) {
      return redirect()
        ->back()
        ->with('error', 'Only draft invoices can be edited.');
    }

    $data = $request->validated();
    // $invoice->update($data);
    $invoice->update([
      'milestone_id' => $data['milestone_id'] ?? null,
      'invoice_date' => $data['invoice_date'],
      'due_date' => $data['due_date'],
      'invoice_type' => $data['invoice_type'],
      'description' => $data['description'],
    ]);
    // Remove old items and re-add new ones
    $invoice->items()->delete();
    $total_with_tax = 0;
    $total_tax = 0;
    $total_amount = 0;
    foreach ($data['items'] as $item) {
      $invoice->items()->create($item);
      $total_tax += $item['tax_amount'];
      $total_with_tax += $item['total_with_tax'];
      $total_amount += $item['amount'];
    }

    $invoice->update(['amount' => $total_amount, 'tax_amount' => $total_tax, 'total_amount' => $total_with_tax]);

    return redirect()
      ->route('pms.invoices.show', [$project->id, $invoice->id])
      ->with('success', 'Invoice updated successfully.');
  }

  public function generate(Project $project, Invoice $invoice)
  {
    if ($invoice->status != Invoice::STATUS_DRAFT) {
      return redirect()
        ->back()
        ->with('error', 'Only draft invoices can be generated.');
    }

    // // Generate invoice number (you might have your own format)
    // $invoiceNumber = 'INV-' . strtoupper(substr($project->project_code, 0, 3)) . '-' . now()->format('Ymd') . '-' . str_pad($project->invoices()->count() + 1, 3, '0', STR_PAD_LEFT);

    $invoice->update([
      // 'invoice_number' => $invoiceNumber,
      'status' => Invoice::STATUS_SENT,
      'generated_by' => Auth::id(),
    ]);

    // TODO: Send invoice to client (email, etc.)

    return redirect()
      ->back()
      ->with('success', 'Invoice generated and sent to client successfully.');
  }

  public function addPayment(Project $project, Invoice $invoice, Request $request)
  {
    $request->validate([
      'amount' => 'required|numeric|min:0.01|max:' . ($invoice->amount - $invoice->paid_amount),
      'payment_date' => 'required|date',
      'payment_method' => 'required|string|max:255',
      'transaction_reference' => 'nullable|string|max:255',
      'notes' => 'nullable|string|max:1000',
      'finance_bank_account_id' => 'nullable|exists:finance_bank_accounts,id',
    ]);

    \Illuminate\Support\Facades\DB::transaction(function () use ($request, $invoice) {
      $payment = $invoice->payments()->create([
        'amount' => $request->amount,
        'payment_date' => $request->payment_date,
        'payment_method' => $request->payment_method,
        'transaction_reference' => $request->transaction_reference,
        'notes' => $request->notes,
        'recorded_by' => Auth::id(),
        'finance_bank_account_id' => $request->finance_bank_account_id,
      ]);

      // Update invoice status if fully paid
      if ($invoice->refresh()->balance_amount <= 0) {
        $invoice->update(['status' => Invoice::STATUS_PAID]);
      }

      // Handle Bank Transaction
      if ($request->finance_bank_account_id) {
        $bankAccount = \App\Models\Finance\FinanceBankAccount::findOrFail($request->finance_bank_account_id);
        $balanceAfter = $bankAccount->current_balance + $request->amount;
        $bankAccount->update(['current_balance' => $balanceAfter]);

        \App\Models\Finance\BankTransaction::create([
          'finance_bank_account_id' => $bankAccount->id,
          'type' => 'credit',
          'amount' => $request->amount,
          'balance_after' => $balanceAfter,
          'transaction_date' => $request->payment_date,
          'category' => 'Invoice Payment',
          'description' => 'Payment for Invoice #' . $invoice->invoice_number,
          'reference_id' => $payment->id,
          'reference_type' => get_class($payment),
        ]);
      }
    });

    return redirect()
      ->back()
      ->with('success', 'Payment recorded successfully.');
  }

  public function markAsPaid(Project $project, Invoice $invoice)
  {
    if ($invoice->status != Invoice::STATUS_SENT) {
      return redirect()
        ->back()
        ->with('error', 'Only sent invoices can be marked as paid.');
    }

    // Create a single payment record for the full amount
    $invoice->payments()->create([
      'amount' => $invoice->amount,
      'payment_date' => now(),
      'payment_method' => 'Full Payment',
      'recorded_by' => Auth::id(),
    ]);

    $invoice->update(['status' => Invoice::STATUS_PAID]);

    return redirect()
      ->back()
      ->with('success', 'Invoice marked as paid successfully.');
  }
  public function importFromPath()
  {
    // Example: file is located in storage/app/imports/JASAR.xlsx
    $filePath = storage_path('app/imports/imports_proforma_tax.xlsx');

    if (!file_exists($filePath)) {
      return 'File not found at path: ' . $filePath;
    }

    Excel::import(new InvoiceImport(), $filePath);

    return 'Invoices imported successfully from file path!';
  }
}