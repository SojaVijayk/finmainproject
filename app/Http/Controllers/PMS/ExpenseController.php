<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\PMS\Expense;
use App\Models\PMS\Project;
use App\Models\PMS\Vendor;
use App\Models\PMS\ExpenseCategory;
use Illuminate\Http\Request;
use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

use App\Models\PMS\Invoice;
use DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
  public function index(Request $request)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $query = Expense::with(['project', 'category', 'vendor', 'creator']);

    // Apply filters
    if ($request->has('project_id') && $request->project_id) {
      $query->where('project_id', $request->project_id);
    }

    if ($request->has('category_id') && $request->category_id) {
      $query->where('category_id', $request->category_id);
    }

    if ($request->has('vendor_id') && $request->vendor_id) {
      $query->where('vendor_id', $request->vendor_id);
    }

    if ($request->has('payment_mode') && $request->payment_mode) {
      $query->where('payment_mode', $request->payment_mode);
    }

    if ($request->has('start_date') && $request->start_date) {
      $query->where('payment_date', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date) {
      $query->where('payment_date', '<=', $request->end_date);
    }

    $expenses = $query->orderBy('payment_date', 'desc')->paginate(20);

    $projects = Project::all();
    $categories = ExpenseCategory::all();
    $vendors = Vendor::all();
    $paymentModes = ['cash', 'bank_transfer', 'cheque', 'upi', 'other'];

    return view('pms.expenses.index', compact('expenses', 'projects', 'categories', 'vendors', 'paymentModes'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function create()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $projects = Project::all();
    $categories = ExpenseCategory::all();
    $vendors = Vendor::all();
    $paymentModes = ['cash', 'bank_transfer', 'cheque', 'upi', 'other'];

    return view('pms.expenses.create', compact('projects', 'categories', 'vendors', 'paymentModes'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'project_id' => 'required|exists:projects,id',
      'category_id' => 'required|exists:expense_categories,id',
      'vendor_id' => 'required|exists:vendors,id',
      'amount' => 'required|numeric|min:0',
      'tax' => 'required|numeric|min:0',
      'payment_mode' => 'required|in:cash,bank_transfer,cheque,upi,other',
      'payment_date' => 'required|date',
      'transaction_reference' => 'nullable|string|max:255',
      'notes' => 'nullable|string',
      // 'finance_bank_account_id' => 'nullable|exists:finance_bank_accounts,id',
    ]);

    // Check if expense exceeds project budget (for non-admins)
    if (
      !auth()
        ->user()
        ->hasRole('admin')
    ) {
      $project = Project::with('proposal')->find($validated['project_id']);
      $totalExpenses = Expense::where('project_id', $validated['project_id'])->sum('total_amount');
      $newTotal = $totalExpenses + $validated['amount'] + $validated['tax'];

      // if ($project->proposal && $newTotal > $project->proposal->budget) {
      //     return back()->withErrors([
      //         'amount' => 'This expense would exceed the project budget. Please contact an administrator.'
      //     ])->withInput();
      // }
    }

    $validated['created_by'] = auth()->id();
    $validated['total_amount'] = $validated['amount'] + $validated['tax'];

    \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
      $expense = Expense::create($validated);

      // Handle Bank Transaction
      if (!empty($validated['finance_bank_account_id'])) {
        $bankAccount = \App\Models\Finance\FinanceBankAccount::findOrFail($validated['finance_bank_account_id']);
        $balanceAfter = $bankAccount->current_balance - $validated['total_amount'];
        $bankAccount->update(['current_balance' => $balanceAfter]);

        \App\Models\Finance\BankTransaction::create([
          'finance_bank_account_id' => $bankAccount->id,
          'type' => 'debit',
          'amount' => $validated['total_amount'],
          'balance_after' => $balanceAfter,
          'transaction_date' => $validated['payment_date'],
          'category' => 'Project Expense',
          'description' => 'Expense for Project', // Could be more specific
          'reference_id' => $expense->id,
          'reference_type' => get_class($expense),
        ]);
      }
    });

    return redirect()
      ->route('pms.expenses.index')
      ->with('success', 'Expense recorded successfully.');
  }

  public function show(Expense $expense)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('pms.expenses.show', compact('expense'), ['pageConfigs' => $pageConfigs]);
  }

  public function edit(Expense $expense)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $projects = Project::all();
    $categories = ExpenseCategory::all();
    $vendors = Vendor::all();
    $paymentModes = ['cash', 'bank_transfer', 'cheque', 'upi', 'other'];

    return view('pms.expenses.edit', compact('expense', 'projects', 'categories', 'vendors', 'paymentModes'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function update(Request $request, Expense $expense)
  {
    $validated = $request->validate([
      'project_id' => 'required|exists:projects,id',
      'category_id' => 'required|exists:expense_categories,id',
      'vendor_id' => 'required|exists:vendors,id',
      'amount' => 'required|numeric|min:0',
      'tax' => 'required|numeric|min:0',
      'payment_mode' => 'required|in:cash,bank_transfer,cheque,upi,other',
      'payment_date' => 'required|date',
      'transaction_reference' => 'nullable|string|max:255',
      'notes' => 'nullable|string',
    ]);

    // Check if expense exceeds project budget (for non-admins)
    if (
      !auth()
        ->user()
        ->hasRole('admin')
    ) {
      $project = Project::with('proposal')->find($validated['project_id']);
      $totalExpenses = Expense::where('project_id', $validated['project_id'])
        ->where('id', '!=', $expense->id)
        ->sum('total_amount');
      $newTotal = $totalExpenses + $validated['amount'] + $validated['tax'];

      // if ($project->proposal && $newTotal > $project->proposal->budget) {
      //     return back()->withErrors([
      //         'amount' => 'This expense would exceed the project budget. Please contact an administrator.'
      //     ])->withInput();
      // }
    }

    $validated['total_amount'] = $validated['amount'] + $validated['tax'];

    $expense->update($validated);

    return redirect()
      ->route('expenses.index')
      ->with('success', 'Expense updated successfully.');
  }

  public function destroy(Expense $expense)
  {
    $expense->delete();

    return redirect()
      ->route('expenses.index')
      ->with('success', 'Expense deleted successfully.');
  }

  public function exportExcel(Request $request)
  {
    return Excel::download(new ExpensesExport($request), 'expenses.xlsx');
  }

  public function exportPdf(Request $request)
  {
    $query = Expense::with(['project', 'category', 'vendor', 'creator']);

    // Apply the same filters as index method
    if ($request->has('project_id') && $request->project_id) {
      $query->where('project_id', $request->project_id);
    }

    if ($request->has('category_id') && $request->category_id) {
      $query->where('category_id', $request->category_id);
    }

    if ($request->has('vendor_id') && $request->vendor_id) {
      $query->where('vendor_id', $request->vendor_id);
    }

    if ($request->has('payment_mode') && $request->payment_mode) {
      $query->where('payment_mode', $request->payment_mode);
    }

    if ($request->has('start_date') && $request->start_date) {
      $query->where('payment_date', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date) {
      $query->where('payment_date', '<=', $request->end_date);
    }

    $expenses = $query->orderBy('payment_date', 'desc')->get();

    $pdf = PDF::loadView('expenses.pdf', compact('expenses'));

    return $pdf->download('expenses.pdf');
  }

  public function report(Request $request)
  {
    $dateRange = $request->input('date_range', 'this_month');

    // Set date range for filtering
    switch ($dateRange) {
      case 'last_month':
        $startDate = Carbon::now()
          ->subMonth()
          ->startOfMonth();
        $endDate = Carbon::now()
          ->subMonth()
          ->endOfMonth();
        break;
      case 'last_quarter':
        $startDate = Carbon::now()
          ->subMonths(3)
          ->startOfMonth();
        $endDate = Carbon::now()
          ->subMonth()
          ->endOfMonth();
        break;
      case 'last_year':
        $startDate = Carbon::now()
          ->subYear()
          ->startOfYear();
        $endDate = Carbon::now()
          ->subYear()
          ->endOfYear();
        break;
      case 'this_month':
      default:
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        break;
    }

    // Budget vs Actual Expense
    $budgetData = Project::with([
      'proposal',
      'expenses' => function ($query) use ($startDate, $endDate) {
        $query->whereBetween('payment_date', [$startDate, $endDate]);
      },
    ])
      ->whereHas('proposal')
      ->get()
      ->map(function ($project) {
        $budget = $project->proposal->budget;
        $actual = $project->expenses->sum('total_amount');
        $variance = $budget - $actual;

        return [
          'project' => $project->name,
          'budget' => $budget,
          'actual' => $actual,
          'variance' => $variance,
          'variance_percent' => $budget > 0 ? ($variance / $budget) * 100 : 0,
        ];
      });

    // Revenue vs Expense
    $revenueData = Project::with([
      'proposal',
      'expenses' => function ($query) use ($startDate, $endDate) {
        $query->whereBetween('payment_date', [$startDate, $endDate]);
      },
      'invoices' => function ($query) use ($startDate, $endDate) {
        $query->whereBetween('invoice_date', [$startDate, $endDate]);
      },
    ])
      ->whereHas('proposal')
      ->get()
      ->map(function ($project) {
        $revenue = $project->invoices->sum('total_amount');
        $expense = $project->expenses->sum('total_amount');
        $profit = $revenue - $expense;

        return [
          'project' => $project->name,
          'revenue' => $revenue,
          'expense' => $expense,
          'profit' => $profit,
          'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
        ];
      });

    // Expense distribution by category
    $categoryData = Expense::join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
      ->select('expense_categories.name as category', DB::raw('SUM(expenses.total_amount) as total'))
      ->whereBetween('payment_date', [$startDate, $endDate])
      ->groupBy('expenses.category_id', 'expense_categories.name')
      ->get();

    // Expense trend over time (last 6 months)
    $trendData = Expense::select(
      DB::raw('YEAR(payment_date) as year'),
      DB::raw('MONTH(payment_date) as month'),
      DB::raw('SUM(total_amount) as total')
    )
      ->where(
        'payment_date',
        '>=',
        Carbon::now()
          ->subMonths(6)
          ->startOfMonth()
      )
      ->groupBy('year', 'month')
      ->orderBy('year', 'asc')
      ->orderBy('month', 'asc')
      ->get()
      ->map(function ($item) {
        return [
          'label' => date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year)),
          'total' => $item->total,
        ];
      });

    // Total metrics for dashboard cards
    $totalExpenses = Expense::whereBetween('payment_date', [$startDate, $endDate])->sum('total_amount');
    $totalRevenue = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('amount');
    $totalProjects = Project::count();
    $avgExpensePerProject = $totalProjects > 0 ? $totalExpenses / $totalProjects : 0;

    return view(
      'pms.expenses.dashboard',
      compact(
        'budgetData',
        'revenueData',
        'categoryData',
        'trendData',
        'totalExpenses',
        'totalRevenue',
        'totalProjects',
        'avgExpensePerProject',
        'dateRange',
        'startDate',
        'endDate'
      )
    );
  }
}