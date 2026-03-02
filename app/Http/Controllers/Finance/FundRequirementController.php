<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Finance\FundRequirement;

class FundRequirementController extends Controller
{
  public function index()
  {
    $requirements = FundRequirement::latest()->paginate(20);
    return view('finance.requirements.index', compact('requirements'));
  }

  public function create()
  {
    return view('finance.requirements.create');
  }

  public function store(Request $request)
  {
    $request->validate([
      'title' => 'required',
      'amount' => 'required|numeric',
      'due_date' => 'required|date',
      'is_recurring' => 'boolean',
    ]);

    FundRequirement::create($request->all());

    return redirect()
      ->route('finance.requirements.index')
      ->with('success', 'Fund Requirement added successfully.');
  }

  public function edit(FundRequirement $requirement)
  {
    return view('finance.requirements.edit', compact('requirement'));
  }

  public function update(Request $request, FundRequirement $requirement)
  {
    $request->validate([
      'title' => 'required',
      'amount' => 'required|numeric',
      'due_date' => 'required|date',
    ]);

    $requirement->update($request->all());

    return redirect()
      ->route('finance.requirements.index')
      ->with('success', 'Fund Requirement updated successfully.');
  }

  public function destroy(FundRequirement $requirement)
  {
    $requirement->delete();
    return redirect()
      ->route('finance.requirements.index')
      ->with('success', 'Fund Requirement deleted successfully.');
  }
}
