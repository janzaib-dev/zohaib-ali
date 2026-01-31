<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\VoucherMaster;
use App\Models\Customer;
use App\Services\VoucherService;

class TransactionService
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Create a Receipt Voucher from a Posted Sale using V2 standard.
     */
    /**
     * Create a Receipt Voucher from a Posted Sale using V2 standard.
     * Supports split payments (multiple accounts).
     */
    public function createReceiptFromSale(Sale $sale, array $accountIds = [], array $amounts = [])
    {
        \Log::info("TransactionService V2: Called for Sale ID {$sale->id}");

        // 1. Validation
        if ($sale->sale_status !== 'posted') {
            \Log::warning('TransactionService: Not posted, aborting.');
            return;
        }

        // Filter out empty or invalid entries
        $accountIds = array_filter($accountIds, function($value) { return !empty($value); });
        
        // Determine if we have valid input arrays, otherwise fallback to Sale Cash
        if (empty($accountIds)) {
            // Fallback: Check if Sale has a cash value (Legacy support or Hidden Input)
             $cash = $sale->cash ?? 0;
            if ($cash > 0) {
                \Log::info("TransactionService: Using fallback Cash amount: $cash");
                $accountIds = [6]; // Default Cash Account (Init ID: 6)
                $amounts = [$cash];
            } else {
                \Log::warning('TransactionService: No payment info provided (Accounts empty and Cash=0), aborting.');
                return;
            }
        }

        try {
            $customerControlAccountId = 4; // Accounts Receivable (Asset) ID: 4
            $totalPaid = 0;
            $lines = [];

            // 2. Prepare Debit Lines (Money In)
            foreach ($accountIds as $index => $accId) {
                $amount = (float)($amounts[$index] ?? 0);
                
                if ($amount >= 0) {
                    $totalPaid += $amount;
                    
                    $lines[] = [
                        'account_id' => $accId,
                        'debit' => $amount,
                        'credit' => 0,
                        'narration' => "Payment received from Invoice #{$sale->invoice_no}",
                    ];
                }
            }

            // if ($totalPaid <= 0) {
            //     \Log::warning('TransactionService: Total payment is 0, skipping voucher.');
            //     return;
            // }

            // 3. Prepare Credit Line (Customer Control - Money Out / Receivable Reduced)
            $lines[] = [
                'account_id' => $customerControlAccountId,
                'debit' => 0,
                'credit' => $totalPaid,
                'narration' => "Payment for Invoice #{$sale->invoice_no}",
            ];

            // 4. Voucher Header
            $voucherData = [
                'voucher_type' => VoucherMaster::TYPE_RECEIPT,
                'date' => now()->format('Y-m-d'),
                'status' => VoucherMaster::STATUS_POSTED, // Auto-post
                'payment_from' => 'Customer',
                'party_type' => Customer::class,
                'party_id' => $sale->customer_id,
                'remarks' => "Auto-Receipt for Sale Invoice #{$sale->invoice_no}. Total: $totalPaid",
            ];

            // 5. Create via VoucherService
            $voucher = $this->voucherService->createVoucher($voucherData, $lines, auth()->id());
            
            // 6. SYNC TO LEGACY CUSTOMER LEDGER (Critical for "Customer Balance" view)
            if ($sale->customer_id) {
                // Fetch latest ledger to get current balance
                // Try-catch to ensure consistency
                $lastEntry = \App\Models\CustomerLedger::where('customer_id', $sale->customer_id)
                    ->lockForUpdate() // Lock to prevent race conditions
                    ->orderBy('id', 'desc')
                    ->first();
                
                $prevBal = $lastEntry ? $lastEntry->closing_balance : 0;
                // Receipt reduces balance (Credit Customer)
                $newBal = $prevBal - $totalPaid;
                
                \Log::info("Legacy Ledger (Receipt): Customer #{$sale->customer_id}. Prev (Expected 9440 range): {$prevBal} - Paid: {$totalPaid} = New: {$newBal}");

                \App\Models\CustomerLedger::create([
                     'customer_id' => $sale->customer_id,
                     'admin_or_user_id' => auth()->id() ?? 1,
                     'description' => "Receipt #{$voucher->voucher_no} for Invoice #{$sale->invoice_no}",
                     'previous_balance' => $prevBal, // Before payment
                     'closing_balance' => $newBal,   // After payment
                     'opening_balance' => 0,
                ]);

                // Update Master Customer Table
                $cust = \App\Models\Customer::find($sale->customer_id);
                if ($cust) {
                    $cust->previous_balance = $newBal;
                    $cust->save();
                }
            }

            \Log::info("TransactionService: V2 Receipt Created: {$voucher->voucher_no} for amount $totalPaid");
            
            return $voucher->voucher_no;

        } catch (\Exception $e) {
            \Log::error('TransactionService V2 Error: '.$e->getMessage());
        }
    }
}
