<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\VoucherMaster;

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
        $accountIds = array_filter($accountIds, function ($value) {
            return ! empty($value);
        });

        // Determine if we have valid input arrays, otherwise fallback to Sale Cash
        if (empty($accountIds)) {
            // Fallback: Check if Sale has a cash value (Legacy support or Hidden Input)
            $cash = $sale->cash ?? 0;
            if ($cash > 0) {
                \Log::info("TransactionService: Using fallback Cash amount: $cash");
                $balanceService = app(\App\Services\BalanceService::class);
                $accountIds = [$balanceService->getCashAccountId()];
                $amounts = [$cash];
            } else {
                \Log::info('TransactionService: No payment info provided (Credit Sale), aborting receipt creation.');

                return;
            }
        }

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            $customerControlAccountId = $balanceService->getAccountsReceivableId();
            $totalPaid = 0;
            $lines = [];

            // 2. Prepare Debit Lines (Money In)
            foreach ($accountIds as $index => $accId) {
                $amount = (float) ($amounts[$index] ?? 0);

                if ($amount > 0) { // Fixed: Only positive amounts
                    $totalPaid += $amount;

                    $lines[] = [
                        'account_id' => $accId,
                        'debit' => $amount,
                        'credit' => 0,
                        'narration' => "Payment received from Invoice #{$sale->invoice_no}",
                    ];
                }
            }

            // Skip if no payment (Credit Sale - customer will pay later)
            if ($totalPaid <= 0) {
                \Log::info('TransactionService: No payment received (Credit Sale), skipping receipt voucher.');

                return;
            }

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

    /**
     * Create a Payment Voucher for a Purchase.
     * Debit: Accounts Payable (Vendor) | Credit: Cash/Bank
     */
    public function createPaymentForPurchase(\App\Models\Purchase $purchase, array $accountIds = [], array $amounts = [])
    {
        \Log::info("TransactionService: Create Payment for Purchase #{$purchase->invoice_no}");

        // Filter valid inputs
        $accountIds = array_filter($accountIds, fn ($val) => ! empty($val));

        if (empty($accountIds)) {
            \Log::info('TransactionService: No payment accounts provided, skipping payment.');

            return;
        }

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            $apAccountId = $balanceService->getAccountsPayableId(); // We need to ensure this method exists

            $totalPaid = 0;
            $lines = [];

            // 1. Prepare Credit Lines (Money Out - Cash/Bank)
            foreach ($accountIds as $index => $accId) {
                $amount = (float) ($amounts[$index] ?? 0);
                if ($amount > 0) {
                    $totalPaid += $amount;
                    $lines[] = [
                        'account_id' => $accId,
                        'debit' => 0,
                        'credit' => $amount, // Money leaving asset
                        'narration' => "Payment for Purchase #{$purchase->invoice_no}",
                    ];
                }
            }

            if ($totalPaid <= 0) {
                return;
            }

            // 2. Prepare Debit Line (Accounts Payable - Liability Decreases)
            $vendorName = '';
            if ($purchase->vendor) {
                $vendorName = $purchase->vendor->name;
            }

            $lines[] = [
                'account_id' => $apAccountId,
                'debit' => $totalPaid,
                'credit' => 0,
                'narration' => "Payment to Vendor {$vendorName}",
            ];

            // 3. Voucher Header
            $voucherData = [
                'voucher_type' => VoucherMaster::TYPE_PAYMENT,
                'date' => now()->format('Y-m-d'),
                'status' => VoucherMaster::STATUS_POSTED,
                'payment_from' => 'Vendor', // Or 'System'
                'party_type' => \App\Models\Vendor::class,
                'party_id' => $purchase->vendor_id,
                'remarks' => "Auto-Payment for Purchase #{$purchase->invoice_no}",
            ];

            // 4. Create Voucher
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            // 5. Update Legacy Vendor Ledger (Optional but recommended for consistency)
            // You might want to move this inside BalanceService or similar if reused
            // For now we rely on Journal Entries, but if you have a legacy table:
            // \App\Models\VendorLedger::create(...)

            // Update Paid Amount in Purchase
            $purchase->paid_amount += $totalPaid;
            $purchase->due_amount = $purchase->net_amount - $purchase->paid_amount;
            $purchase->save();

            \Log::info("Payment Voucher Created for Purchase #{$purchase->invoice_no}");

        } catch (\Exception $e) {
            \Log::error('TransactionService Payment Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function createPurchaseVoucher(\App\Models\Purchase $purchase)
    {
        \Log::info("TransactionService: Create Voucher for Purchase #{$purchase->invoice_no}");

        try {
            $balanceService    = app(\App\Services\BalanceService::class);
            $purchaseAccountId = $balanceService->getPurchaseExpenseId(); // "Purchase" COA
            $apAccountId       = $balanceService->getAccountsPayableId();

            $extraCost     = (float) ($purchase->extra_cost ?? 0);
            // Pure purchase price = net_amount minus extra_cost
            $purchasePrice = max(0, (float) $purchase->net_amount - $extraCost);

            $lines = [];

            // 1. Debit "Purchase" account — pure inventory cost only (no extra)
            $lines[] = [
                'account_id' => $purchaseAccountId,
                'debit'      => $purchasePrice,
                'credit'     => 0,
                'narration'  => "Purchase Invoice #{$purchase->invoice_no}",
            ];

            // 2. If extra_cost > 0, also debit "Purchase Expensive" for the additional cost
            if ($extraCost > 0) {
                $purchaseExpensiveId = $balanceService->getPurchaseExpensiveId();
                $lines[] = [
                    'account_id' => $purchaseExpensiveId,
                    'debit'      => $extraCost,
                    'credit'     => 0,
                    'narration'  => "Extra Cost on Purchase #{$purchase->invoice_no}",
                ];
            }

            // 3. Credit Accounts Payable full net_amount (vendor is owed everything)
            $lines[] = [
                'account_id' => $apAccountId,
                'debit'      => 0,
                'credit'     => $purchase->net_amount,
                'narration'  => "Payable to Vendor " . ($purchase->vendor->name ?? ''),
            ];

            // 4. Voucher Header
            $voucherData = [
                'voucher_type' => \App\Models\VoucherMaster::TYPE_JOURNAL,
                'date'         => $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'status'       => \App\Models\VoucherMaster::STATUS_POSTED,
                'party_type'   => \App\Models\Vendor::class,
                'party_id'     => $purchase->vendor_id,
                'remarks'      => "Purchase Voucher #{$purchase->invoice_no}" . ($extraCost > 0 ? " (Extra Cost: {$extraCost})" : ''),
            ];

            // 5. Create the V2 Voucher (journal entries)
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            \Log::info("Purchase Voucher Created for #{$purchase->invoice_no}. Price: {$purchasePrice}, Extra: {$extraCost}");

            // 6. Auto-create a legacy ExpenseVoucher for extra_cost so it appears in Expense Voucher list
            if ($extraCost > 0) {
                $this->createExpenseVoucherForExtraCost($purchase, $extraCost);
            }

        } catch (\Exception $e) {
            \Log::error('TransactionService Purchase Voucher Error: ' . $e->getMessage());
        }
    }

    /**
     * Auto-create a legacy ExpenseVoucher record for the extra_cost on a purchase.
     * This makes the expense appear in the existing Expense Voucher listing page.
     * Accounting: Dr. Purchase Expensive (PURCHASE_EXP) — already done in createPurchaseVoucher V2 lines above.
     */
    public function createExpenseVoucherForExtraCost(\App\Models\Purchase $purchase, float $extraCost)
    {
        try {
            $balanceService      = app(\App\Services\BalanceService::class);
            $purchaseExpensiveId = $balanceService->getPurchaseExpensiveId();

            $evid = \App\Models\ExpenseVoucher::generateInvoiceNo();

            // Find or create the narration for purchase extra costs
            $narration = \App\Models\Narration::firstOrCreate(
                ['narration'    => 'Purchase Extra Cost', 'expense_head' => 'Expense voucher'],
                ['narration'    => 'Purchase Extra Cost', 'expense_head' => 'Expense voucher']
            );

            \App\Models\ExpenseVoucher::create([
                'evid'             => $evid,
                'entry_date'       => $purchase->purchase_date ?? now()->toDateString(),
                'type'             => 'vendor',
                'party_id'         => $purchase->vendor_id,
                'remarks'          => "Auto: Purchase Expensive for Invoice #{$purchase->invoice_no}",
                'narration_id'     => json_encode([(string) $narration->id]),
                'row_account_head' => json_encode([null]),
                'row_account_id'   => json_encode([$purchaseExpensiveId]),
                'amount'           => json_encode([$extraCost]),
                'total_amount'     => $extraCost,
            ]);

            // Update legacy Account Ledger (Row Side = Plus)
            $rowAccount = \App\Models\Account::find($purchaseExpensiveId);
            if ($rowAccount) {
                $rowAccount->opening_balance += $extraCost;
                $rowAccount->save();
            }

            // Update legacy Vendor Ledger (Party Side = Minus)
            $ledger = \App\Models\VendorLedger::where('vendor_id', $purchase->vendor_id)->latest()->first();
            if ($ledger) {
                \App\Models\VendorLedger::create([
                    'vendor_id'         => $purchase->vendor_id,
                    'admin_or_user_id'  => auth()->id() ?? 1,
                    'opening_balance'   => $ledger->opening_balance ?? 0,
                    'previous_balance'  => $ledger->closing_balance,
                    'closing_balance'   => $ledger->closing_balance - $extraCost,
                ]);
            }

            \Log::info("Auto ExpenseVoucher created for Purchase #{$purchase->invoice_no}, Extra Cost: {$extraCost}");

        } catch (\Exception $e) {
            \Log::error('Auto ExpenseVoucher Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a Purchase Return Voucher (Debit Note).
     * Debit: Accounts Payable (Vendor) | Credit: Purchase Return / Inventory
     */
    public function createPurchaseReturnVoucher(\App\Models\PurchaseReturn $return)
    {
        \Log::info("TransactionService: Create Voucher for Purchase Return #{$return->return_invoice}");

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            // Use Purchase Expense Account (Contra) or a specific Return Account
            $expenseAccountId = $balanceService->getPurchaseExpenseId(); 
            $apAccountId = $balanceService->getAccountsPayableId();

            $lines = [];

            // 1. Debit Accounts Payable (Vendor Liability Reduces)
            $lines[] = [
                'account_id' => $apAccountId,
                'debit' => $return->net_amount,
                'credit' => 0,
                'narration' => "Debit Note for Return #{$return->return_invoice}",
            ];

            // 2. Credit Purchase Expense (Inventory Value Reduces)
            $lines[] = [
                'account_id' => $expenseAccountId,
                'debit' => 0,
                'credit' => $return->net_amount,
                'narration' => "Purchase Return #{$return->return_invoice}",
            ];

            // 3. Voucher Header
            // Use Journal Type or a specific 'Debit Note' type if available. 
            // Using TYPE_JOURNAL for general ledger adjustment.
            $voucherData = [
                'voucher_type' => \App\Models\VoucherMaster::TYPE_JOURNAL, 
                'date' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'status' => \App\Models\VoucherMaster::STATUS_POSTED,
                'party_type' => \App\Models\Vendor::class,
                'party_id' => $return->vendor_id,
                'remarks' => $return->remarks ?? "Purchase Return #{$return->return_invoice}",
            ];

            // 4. Create Voucher
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            \Log::info("Purchase Return Voucher Created for Invoice #{$return->return_invoice}");

        } catch (\Exception $e) {
            \Log::error('TransactionService Purchase Return Voucher Error: ' . $e->getMessage());
        }
    }

    /**
     * Create Journal Voucher for Sale Return (Credit Note)
     * Dr. Sales Revenue (Reduces Income)
     * Cr. Accounts Receivable (Reduces Customer Debt)
     */
    public function createSaleReturnVoucher(\App\Models\SaleReturn $return)
    {
        \Log::info("TransactionService: Create Voucher for Sale Return #{$return->return_invoice}");

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            // Sales Revenue Account
            $salesRevenueId = $balanceService->getSalesRevenueId(); 
            $arAccountId = $balanceService->getAccountsReceivableId();

            $lines = [];

            // 1. Debit Sales Revenue (Income Reduces)
            $lines[] = [
                'account_id' => $salesRevenueId,
                'debit' => $return->net_amount,
                'credit' => 0,
                'narration' => "Credit Note for Return #{$return->return_invoice}",
            ];

            // 2. Credit Accounts Receivable (Customer Debt Reduces)
            $lines[] = [
                'account_id' => $arAccountId,
                'debit' => 0,
                'credit' => $return->net_amount,
                'narration' => "Sale Return #{$return->return_invoice}",
            ];

            // 3. Voucher Header
            $voucherData = [
                'voucher_type' => \App\Models\VoucherMaster::TYPE_JOURNAL, 
                'date' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'status' => \App\Models\VoucherMaster::STATUS_POSTED,
                'party_type' => \App\Models\Customer::class,
                'party_id' => $return->customer_id,
                'remarks' => $return->remarks ?? "Sale Return #{$return->return_invoice}",
            ];

            // 4. Create Voucher
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            \Log::info("Sale Return Voucher Created for Invoice #{$return->return_invoice}");

        } catch (\Exception $e) {
            \Log::error('TransactionService Sale Return Voucher Error: ' . $e->getMessage());
        }
    }
}
