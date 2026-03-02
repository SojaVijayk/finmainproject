<?php

namespace App\Imports;

use App\Models\PMS\Invoice;
use App\Models\PMS\InvoiceItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Log;

class InvoiceImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            try {
                // FIRST: Let's debug what's actually in the row
                if ($index === 0) {
                    logger('First row data:', $row->toArray());
                    logger('Available keys:', array_keys($row->toArray()));
                }

                // Use the proper column names from your Excel file
                // Based on your Excel structure, the columns are:
                // project_id, project_code, invoice_type, invoice_number, invoice_date, due_date, amount, tax_amount, total_amount, description, status, created_at, updated_at

                $projectId = $row['project_id'] ?? $row['project_id'] ?? null;
                $invoiceNumber = $row['invoice_number'] ?? $row['invoice_number'] ?? null;
                $invoiceDate = $row['invoice_date'] ?? $row['invoice_date'] ?? null;
                $dueDate = $row['due_date'] ?? $row['due_date'] ?? null;
                $amount = $row['amount'] ?? $row['amount'] ?? 0;
                $taxAmount = $row['tax_amount'] ?? $row['tax_amount'] ?? 0;
                $totalAmount = $row['total_amount'] ?? $row['total_amount'] ?? 0;
                $description = $row['description'] ?? $row['description'] ?? 'Imported invoice';
                $invoice_type = $row['invoice_type'] ?? $row['invoice_type'] ?? 2;
                 $status = $row['status'] ?? $row['status'] ?? 1;

                // Debug the values
                logger("Row {$index} values:", [
                    'project_id' => $projectId,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'amount' => $amount,
                    'type_project_id' => gettype($projectId),
                    'type_invoice_date' => gettype($invoiceDate),
                    'invoice_type'=> $invoice_type,
                     'status'=> $status
                ]);

                // Skip if essential fields are empty
                if (empty($projectId) || empty($invoiceNumber) || empty($invoiceDate)) {
                    logger("Skipping row {$index} - Missing required fields");
                    $skipped++;
                    continue;
                }

                // Parse dates
                $parsedInvoiceDate = $this->parseExcelDate($invoiceDate);
                $parsedDueDate = $this->parseExcelDate($dueDate);

                // If date parsing fails, use fallbacks
                // if (!$parsedInvoiceDate) {
                //     $parsedInvoiceDate = now()->format('Y-m-d');
                // }
                // if (!$parsedDueDate) {
                //     $parsedDueDate = Carbon::parse($parsedInvoiceDate)->addDays(30)->format('Y-m-d');
                // }

                // Convert to integers and floats
                $projectId = (int) $projectId;
                $amount = floatval($amount);
                $taxAmount = floatval($taxAmount);
                $totalAmount = floatval($totalAmount);

                // Validate project_id
                if ($projectId <= 0) {
                    logger("Skipping row {$index} - Invalid project ID: {$projectId}");
                    $skipped++;
                    continue;
                }

                logger("Creating invoice for project {$projectId} with number {$invoiceNumber}");

                // Create Invoice
                $invoice = Invoice::create([
                    'project_id'     => $projectId,
                    'milestone_id'   => null,
                    'invoice_type'   =>  $invoice_type, // TAXINVOICE
                    'invoice_number' => $invoiceNumber,
                    'invoice_date'   => $invoiceDate,
                    'due_date'       => $dueDate,
                    'amount'         => $amount,
                    'tax_amount'     => $taxAmount,
                    'total_amount'   => $totalAmount,
                    'description'    => $description,
                    'status'         => $status,
                    'requested_by'   => 44,
                    'generated_by'   => 44,
                ]);

                // Calculate tax percentage
                $taxPercentage = 0;
                if ($amount > 0) {
                    $taxPercentage = ($taxAmount / $amount) * 100;
                }

                // Create Invoice Item
                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'description'    => $description,
                    'amount'         => $amount,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount'     => $taxAmount,
                    'total_with_tax' => $totalAmount,
                ]);

                $imported++;
                logger("Successfully imported invoice {$invoiceNumber}");

            } catch (\Exception $e) {
                logger("Error importing row {$index}: " . $e->getMessage());
                logger("Row data that caused error:", $row->toArray());
                $skipped++;
                continue;
            }
        }

        logger("Import completed: {$imported} imported, {$skipped} skipped");
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // If it's an Excel serial date (numeric)
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
            }

            $value = (string) $value;

            // Handle format: 2025-07-25 00:00:00 (from your Excel)
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d');
            }

            // Handle format: 2025-07-25
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return $value; // Already in correct format
            }

            // Handle format: 07/10/2025 (d/m/Y)
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            // Try generic parsing as last resort
            return Carbon::parse($value)->format('Y-m-d');

        } catch (\Exception $e) {
            logger("Failed to parse date: {$value} - Error: " . $e->getMessage());
            return null;
        }
    }
}
