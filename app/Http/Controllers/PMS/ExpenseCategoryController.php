<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;


use App\Models\PMS\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $categories = ExpenseCategory::paginate(20);
        return view('pms.expenses.categories.index', compact('categories'),['pageConfigs'=> $pageConfigs]);
    }

    public function create()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        return view('pms.expenses.categories.create',['pageConfigs'=> $pageConfigs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories',
            'description' => 'nullable|string'
        ]);

        ExpenseCategory::create($validated);

        return redirect()->route('pms.expense-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        return view('pms.expenses.categories.edit', compact('expenseCategory'),['pageConfigs'=> $pageConfigs]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string'
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('pms.expense-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return redirect()->route('pms.expense-categories.index')
                ->with('error', 'Cannot delete category with associated expenses.');
        }

        $expenseCategory->delete();

        return redirect()->route('pms.expense-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}