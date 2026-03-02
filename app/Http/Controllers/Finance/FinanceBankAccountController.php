<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Finance\FinanceBankAccount;

class FinanceBankAccountController extends Controller
{
  public function index()
  {
    $accounts = FinanceBankAccount::all();
     $pageConfigs = ['myLayout' => 'horizontal'];
    return view('finance.accounts.index', compact('accounts'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function create()
  {
     $pageConfigs = ['myLayout' => 'horizontal'];
    return view('finance.accounts.create', [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'bank_name' => 'required',
      'account_name' => 'required',
      'account_number' => 'required|unique:finance_bank_accounts',
      'opening_balance' => 'required|numeric',
    ]);

    $account = FinanceBankAccount::create($request->all());
    $account->current_balance = $request->opening_balance;
    $account->save();

    return redirect()
      ->route('pms.finance.accounts.index')
      ->with('success', 'Bank Account created successfully.');
  }

  public function edit(FinanceBankAccount $account)
  {
    return view('finance.accounts.edit', compact('account'));
  }

  public function update(Request $request, FinanceBankAccount $account)
  {
    $request->validate([
      'bank_name' => 'required',
      'account_name' => 'required',
      'account_number' => 'required|unique:finance_bank_accounts,account_number,' . $account->id,
    ]);

    $account->update($request->all());

    return redirect()
      ->route('pms.finance.accounts.index')
      ->with('success', 'Bank Account updated successfully.');
  }

  public function destroy(FinanceBankAccount $account)
  {
    $account->delete();
    return redirect()
      ->route('pms.finance.accounts.index')
      ->with('success', 'Bank Account deleted successfully.');
  }
}