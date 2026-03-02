<?php

namespace App\Imports;

use App\Models\Finance\BankTransaction;
use App\Models\Finance\FinanceBankAccount;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class BankTransactionImport implements ToModel, WithHeadingRow
{
  protected $bankAccountId;

  public function __construct($bankAccountId)
  {
    $this->bankAccountId = $bankAccountId;
  }

  public function model(array $row)
  {
    // Simple validation or skip empty
    if (!isset($row['amount']) || !isset($row['transaction_date'])) {
      return null;
    }

    $account = FinanceBankAccount::find($this->bankAccountId);
    if (!$account) {
      return null;
    }

    $amount = (float) $row['amount'];
    $type = strtolower($row['type']); // credit or debit

    // Update balance
    if ($type === 'credit') {
      $account->current_balance += $amount;
    } elseif ($type === 'debit') {
      $account->current_balance -= $amount;
    }
    $account->save();

    return new BankTransaction([
      'finance_bank_account_id' => $this->bankAccountId,
      'type' => $type,
      'amount' => $amount,
      'balance_after' => $account->current_balance,
      'transaction_date' => Carbon::parse($row['transaction_date']),
      'category' => $row['category'] ?? 'Manual Import',
      'description' => $row['description'] ?? null,
    ]);
  }
}
