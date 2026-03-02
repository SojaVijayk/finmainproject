<?php

namespace App\Imports;

use App\Models\Finance\DailyBankBalance;
use App\Models\Finance\FinanceBankAccount;
use App\Models\Finance\BankTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DailyBankBalanceImport implements ToCollection
{
  public function collection(Collection $rows)
  {
    // Headers are expected on Row 1 (Index 0). Data starts from Row 2 (Index 1).

    $accounts = FinanceBankAccount::all()->keyBy('account_number');
    $processedDate = null;

    foreach ($rows as $index => $row) {
      // Skip header row
      if ($index === 0) {
        continue;
      }

      // Columns based on new format:
      // 0: Date
      // 1: Account
      // 2: Opening Balance
      // 3: Receipts
      // 4: Payments
      // 5: Closing Balance

      $dateVal = $row[0] ?? null;
      $accountNum = trim((string) ($row[1] ?? ''));

      // If essential data is missing, skip
      if (!$dateVal || !$accountNum) {
        continue;
      }

      // Parse Date
      try {
        // Handle Excel serial date or string date
        if (is_numeric($dateVal)) {
          $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateVal)->format('Y-m-d');
        } else {
          $date = Carbon::parse($dateVal)->format('Y-m-d');
        }
      } catch (\Exception $e) {
        Log::warning("Skipping row $index: Invalid date format: $dateVal");
        continue;
      }

      $processedDate = $date;

      // Find Account
      if (!$accounts->has($accountNum)) {
        Log::warning("Skipping row $index: Account $accountNum not found.");
        continue;
      }

      $account = $accounts->get($accountNum);

      // Parse Numbers
      $openingBalance = $this->parseNumber($row[2]);
      $receipts = $this->parseNumber($row[3]);
      $payments = $this->parseNumber($row[4]);
      $closingBalance = $this->parseNumber($row[5]);

      DB::transaction(function () use ($account, $date, $openingBalance, $receipts, $payments, $closingBalance) {
        // 1. Update/Create DailyBankBalance
        DailyBankBalance::updateOrCreate(
          [
            'finance_bank_account_id' => $account->id,
            'date' => $date,
          ],
          [
            'opening_balance' => $openingBalance,
            'receipts' => $receipts,
            'payments' => $payments,
            'closing_balance' => $closingBalance,
          ]
        );

        // 2. Update FinanceBankAccount Current Balance (Set to Closing Balance of this entry, assume latest import is truth)
        // Note: If importing old dates, this might rewrite current balance to old state.
        // However, user usually imports "Daily", so it's likely the latest state.
        $account->update(['current_balance' => $closingBalance]);

        // 3. Create Transactions (Idempotency Check)
        // We treat "Receipts" as Credits and "Payments" as Debits.

        // Credit (Receipts)
        if ($receipts > 0) {
          $this->createTransaction($account, $date, 'credit', $receipts, 'Daily Import - Receipts');
        }

        // Debit (Payments)
        if ($payments > 0) {
          $this->createTransaction($account, $date, 'debit', $payments, 'Daily Import - Payments');
        }
      });
    }
  }

  private function createTransaction($account, $date, $type, $amount, $defaultDesc)
  {
    // Check if a similar transaction already exists for this day/account/amount/type to avoid dups on re-import
    $exists = BankTransaction::where('finance_bank_account_id', $account->id)
      ->whereDate('transaction_date', $date)
      ->where('type', $type)
      ->where('amount', $amount)
      ->where('category', 'Daily Import')
      ->exists();

    if (!$exists) {
      BankTransaction::create([
        'finance_bank_account_id' => $account->id,
        'type' => $type,
        'amount' => $amount,
        'balance_after' => $account->current_balance, // Approx, might not be exact if multiple trans in one day
        'transaction_date' => $date,
        'category' => 'Daily Import',
        'description' => $defaultDesc,
      ]);
    }
  }

  private function parseNumber($value)
  {
    if (is_string($value)) {
      $value = str_replace([',', ' '], '', $value);
    }
    return is_numeric($value) ? (float) $value : 0;
  }
}
