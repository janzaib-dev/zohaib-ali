<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\VoucherDetail;
use App\Models\VoucherMaster;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Get customer balance from journal entries
     * Positive = Customer owes money (Dr)
     * Negative = Customer has advance/credit (Cr)
     */
    public function getCustomerBalance(int $customerId): float
    {
        $customer = Customer::find($customerId);
        if (! $customer) {
            return 0;
        }

        // Opening balance from customer master
        $openingBalance = (float) ($customer->opening_balance ?? 0);

        // Sum of all journal entries for this customer
        $journalBalance = JournalEntry::where('party_type', Customer::class)
            ->where('party_id', $customerId)
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as balance')
            ->value('balance') ?? 0;

        return $openingBalance + $journalBalance;
    }

    /**
     * Get customer balance before a specific date
     */
    public function getCustomerBalanceBeforeDate(int $customerId, string $date): float
    {
        $customer = Customer::find($customerId);
        if (! $customer) {
            return 0;
        }

        $openingBalance = (float) ($customer->opening_balance ?? 0);

        $journalBalance = JournalEntry::where('party_type', Customer::class)
            ->where('party_id', $customerId)
            ->where('entry_date', '<', $date)
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as balance')
            ->value('balance') ?? 0;

        return $openingBalance + $journalBalance;
    }

    /**
     * Get customer ledger entries for a date range
     */
    public function getCustomerLedger(int $customerId, string $startDate, string $endDate): array
    {
        $customer = Customer::find($customerId);
        if (! $customer) {
            return [
                'customer' => null,
                'opening_balance' => 0,
                'transactions' => [],
            ];
        }

        // Get opening balance (balance before start date)
        $openingBalance = $this->getCustomerBalanceBeforeDate($customerId, $startDate);

        // Get journal entries in range
        $entries = JournalEntry::where('party_type', Customer::class)
            ->where('party_id', $customerId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('id', 'asc')
            ->get();

        // Calculate running balance
        $runningBalance = $openingBalance;
        $transactions = $entries->map(function ($entry) use (&$runningBalance) {
            $runningBalance += ($entry->debit - $entry->credit);

            return [
                'id' => $entry->id,
                'date' => $entry->entry_date,
                'description' => $entry->description,
                'debit' => $entry->debit,
                'credit' => $entry->credit,
                'balance' => $runningBalance,
                'source_type' => $entry->source_type,
                'source_id' => $entry->source_id,
            ];
        });

        return [
            'customer' => $customer,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get vendor balance from journal entries
     * Positive = We owe vendor (Cr)
     * Negative = Vendor owes us (Dr) - rare
     */
    public function getVendorBalance(int $vendorId): float
    {
        $vendor = \App\Models\Vendor::find($vendorId);
        if (! $vendor) {
            return 0;
        }

        // Opening balance from vendor master
        $openingBalance = (float) ($vendor->opening_balance ?? 0);

        // Sum of all journal entries for this vendor
        // For vendors: Credit increases balance (we owe more)
        //              Debit decreases balance (we pay)
        $journalBalance = JournalEntry::where('party_type', \App\Models\Vendor::class)
            ->where('party_id', $vendorId)
            ->selectRaw('COALESCE(SUM(credit) - SUM(debit), 0) as balance')
            ->value('balance');

        $journalBalance = $journalBalance ?? 0;

        return $openingBalance + $journalBalance;
    }

    /**
     * Get vendor balance before a specific date
     */
    public function getVendorBalanceBeforeDate(int $vendorId, string $date): float
    {
        $vendor = \App\Models\Vendor::find($vendorId);
        if (! $vendor) {
            return 0;
        }

        $openingBalance = (float) ($vendor->opening_balance ?? 0);

        $journalBalance = JournalEntry::where('party_type', \App\Models\Vendor::class)
            ->where('party_id', $vendorId)
            ->where('entry_date', '<', $date)
            ->selectRaw('COALESCE(SUM(credit) - SUM(debit), 0) as balance')
            ->value('balance');

        $journalBalance = $journalBalance ?? 0;

        return $openingBalance + $journalBalance;
    }

    /**
     * Get Financial Summary for Dashboard
     */
    public function getFinancialSummary(string $startDate, string $endDate): array
    {
        // 1. Sales Revenue (Credit entries in Sales Account)
        // Assuming Sales Account ID is 4 (Standard) or fetch by code
        $salesHeadId = 4; // Income
        $sales = JournalEntry::whereHas('account', function ($q) use ($salesHeadId) {
            $q->where('head_id', $salesHeadId);
        })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum('credit');

        // 2. Purchase Expense (Debit entries in Expense Account)
        $expenseHeadId = 3; // Expense
        $purchases = JournalEntry::whereHas('account', function ($q) use ($expenseHeadId) {
            $q->where('head_id', $expenseHeadId);
        })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum('debit');

        // 3. Total Receivables (Money people owe us)
        $receivables = \App\Models\CustomerLedger::sum('closing_balance'); // Use legacy for now or calculate from journals

        // 4. Total Payables (Money we owe vendors)
        // Calculate from Journal Entries since we just implemented it
        $payables = JournalEntry::where('party_type', \App\Models\Vendor::class)
            ->selectRaw('SUM(credit) - SUM(debit) as balance')
            ->value('balance') ?? 0;

        return [
            'sales' => $sales,
            'purchases' => $purchases,
            'receivables' => $receivables,
            'payables' => $payables,
            'net_cash_flow' => $sales - $purchases, // Rough estimate
        ];
    }

    /**
     * Get vendor ledger entries for a date range
     */
    public function getVendorLedger(int $vendorId, string $startDate, string $endDate): array
    {
        $vendor = \App\Models\Vendor::find($vendorId);
        if (! $vendor) {
            return [
                'vendor' => null,
                'opening_balance' => 0,
                'transactions' => [],
            ];
        }

        // Get opening balance (balance before start date)
        $openingBalance = $this->getVendorBalanceBeforeDate($vendorId, $startDate);

        // Get journal entries in range
        $entries = JournalEntry::where('party_type', \App\Models\Vendor::class)
            ->where('party_id', $vendorId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate running balance
        $runningBalance = $openingBalance;
        $transactions = $entries->map(function ($entry) use (&$runningBalance) {
            // For vendors: Credit increases, Debit decreases
            $runningBalance += ($entry->credit - $entry->debit);

            return [
                'id' => $entry->id,
                'date' => $entry->entry_date,
                'description' => $entry->description,
                'debit' => $entry->debit,
                'credit' => $entry->credit,
                'balance' => $runningBalance,
                'source_type' => $entry->source_type,
                'source_id' => $entry->source_id,
            ];
        });

        return [
            'vendor' => $vendor,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'transactions' => $transactions,
        ];
    }

    /**
     * Create a Receipt Voucher using VoucherMaster + JournalEntry
     */
    public function createReceiptVoucher(
        Customer $customer,
        float $amount,
        int $cashAccountId,
        string $date,
        ?string $description = null,
        $source = null
    ): VoucherMaster {
        return DB::transaction(function () use ($customer, $amount, $cashAccountId, $date, $description) {

            // 1. Generate voucher number
            $voucherNo = $this->generateVoucherNo('receipt');

            // 2. Create VoucherMaster
            $voucher = VoucherMaster::create([
                'voucher_type' => VoucherMaster::TYPE_RECEIPT,
                'voucher_no' => $voucherNo,
                'date' => $date,
                'party_type' => Customer::class,
                'party_id' => $customer->id,
                'total_amount' => $amount,
                'remarks' => $description ?? "Receipt from {$customer->customer_name}",
                'status' => VoucherMaster::STATUS_POSTED,
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            // 3. Create VoucherDetails (Dr Cash, Cr Receivable)
            $receivableAccountId = $this->getAccountsReceivableId();

            // Debit Cash/Bank
            VoucherDetail::create([
                'voucher_master_id' => $voucher->id,
                'account_id' => $cashAccountId,
                'debit' => $amount,
                'credit' => 0,
                'narration' => 'Cash/Bank received',
            ]);

            // Credit Accounts Receivable
            VoucherDetail::create([
                'voucher_master_id' => $voucher->id,
                'account_id' => $receivableAccountId,
                'debit' => 0,
                'credit' => $amount,
                'narration' => 'Customer payment received',
            ]);

            // 4. Create Journal Entries
            $journalService = app(JournalEntryService::class);

            // Dr Cash
            $journalService->recordEntry(
                $voucher,
                $cashAccountId,
                $amount,
                0,
                $description ?? "Receipt #{$voucherNo}",
                $date
            );

            // Cr Receivable (with Customer party)
            $journalService->recordEntry(
                $voucher,
                $receivableAccountId,
                0,
                $amount,
                $description ?? "Receipt #{$voucherNo}",
                $date,
                $customer
            );

            return $voucher;
        });
    }

    /**
     * Create a Sale Invoice Voucher
     */
    public function createSaleVoucher(
        Customer $customer,
        float $amount,
        string $invoiceNo,
        string $date
    ): VoucherMaster {
        return DB::transaction(function () use ($customer, $amount, $invoiceNo, $date) {

            $voucherNo = $this->generateVoucherNo('journal');

            $voucher = VoucherMaster::create([
                'voucher_type' => VoucherMaster::TYPE_JOURNAL,
                'voucher_no' => $voucherNo,
                'date' => $date,
                'party_type' => Customer::class,
                'party_id' => $customer->id,
                'total_amount' => $amount,
                'remarks' => "Sale Invoice #{$invoiceNo}",
                'status' => VoucherMaster::STATUS_POSTED,
                'created_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $receivableAccountId = $this->getAccountsReceivableId();
            $salesAccountId = $this->getSalesRevenueId();

            // Dr Receivable
            VoucherDetail::create([
                'voucher_master_id' => $voucher->id,
                'account_id' => $receivableAccountId,
                'debit' => $amount,
                'credit' => 0,
                'narration' => "Sale Invoice #{$invoiceNo}",
            ]);

            // Cr Sales Revenue
            VoucherDetail::create([
                'voucher_master_id' => $voucher->id,
                'account_id' => $salesAccountId,
                'debit' => 0,
                'credit' => $amount,
                'narration' => "Sale Invoice #{$invoiceNo}",
            ]);

            // Journal Entries
            $journalService = app(JournalEntryService::class);

            // Dr Receivable with customer party
            $journalService->recordEntry(
                $voucher,
                $receivableAccountId,
                $amount,
                0,
                "Sale Invoice #{$invoiceNo}",
                $date,
                $customer
            );

            // Cr Sales
            $journalService->recordEntry(
                $voucher,
                $salesAccountId,
                0,
                $amount,
                "Sale Invoice #{$invoiceNo}",
                $date
            );

            return $voucher;
        });
    }

    /**
     * Generate unique voucher number
     */
    private function generateVoucherNo(string $type): string
    {
        $prefix = match ($type) {
            'receipt' => 'RV',
            'payment' => 'PV',
            'expense' => 'EV',
            'journal' => 'JV',
            default => 'V',
        };

        $year = date('Y');
        $lastVoucher = VoucherMaster::where('voucher_type', $type)
            ->where('voucher_no', 'like', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastVoucher) {
            $lastNum = (int) substr($lastVoucher->voucher_no, -4);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return "{$prefix}-{$year}-".str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get Accounts Receivable account ID
     */
    public function getAccountsReceivableId(): int
    {
        $account = Account::where('title', 'like', '%Receivable%')
            ->orWhere('account_code', 'AR')
            ->first();

        if (! $account) {
            $account = Account::create([
                'title' => 'Accounts Receivable',
                'account_code' => 'AR',
                'type' => 'Debit',
                'head_id' => null,
                'opening_balance' => 0,
                'status' => 1,
            ]);
        }

        return $account->id;
    }

    /**
     * Get Sales Revenue account ID
     */
    public function getSalesRevenueId(): int
    {
        $account = Account::where('title', 'like', '%Sales%')
            ->orWhere('account_code', 'SALES')
            ->first();

        if (! $account) {
            $account = Account::create([
                'title' => 'Sales Revenue',
                'account_code' => 'SALES',
                'type' => 'Credit', // Income is Credit nature
                'head_id' => null,
                'opening_balance' => 0,
                'status' => 1,
            ]);
        }

        return $account->id;
    }

    /**
     * Get Cash account ID
     */
    public function getCashAccountId(): int
    {
        $account = Account::where('title', 'like', '%Cash%')
            ->orWhere('account_code', 'CASH')
            ->first();

        if (! $account) {
            $account = Account::create([
                'title' => 'Cash Account',
                'account_code' => 'CASH',
                'type' => 'Debit', // Asset is Debit nature
                'head_id' => null,
                'opening_balance' => 0,
                'status' => 1,
            ]);
        }

        return $account->id;
    }

    /**
     * Get Accounts Payable account ID (Liability)
     * Auto-creates if missing.
     */
    public function getAccountsPayableId(): int
    {
        $account = Account::where('title', 'like', '%Payable%')
            ->orWhere('account_code', 'AP')
            ->first();

        if (! $account) {
            \Log::info("BalanceService: 'Accounts Payable' missing, creating it.");
            // Ideally should find a Liability Head, but for now create without head or default
            $account = Account::create([
                'title' => 'Accounts Payable',
                'account_code' => 'AP',
                'type' => 'Credit', // Liability is Cr nature
                'head_id' => null, // Or look for Liability head
                'opening_balance' => 0,
                'status' => 1,
                'is_active' => 1,
            ]);
        }

        return $account->id;
    }

    /**
     * Get Purchase Expense account ID (Expense)
     * Auto-creates if missing.
     */
    public function getPurchaseExpenseId(): int
    {
        $account = Account::where('title', 'like', '%Purchase%')
            ->orWhere('title', 'like', '%Cost of Goods%')
            ->orWhere('account_code', 'PURCHASE')
            ->orWhere('account_code', 'COGS')
            ->first();

        if (! $account) {
            \Log::info("BalanceService: 'Purchase Expense' missing, creating it.");
            $account = Account::create([
                'title' => 'Purchase Expense',
                'account_code' => 'PURCHASE',
                'type' => 'Debit', // Expense is Dr nature
                'head_id' => null, // Or look for Expense head
                'opening_balance' => 0,
                'status' => 1,
                'is_active' => 1,
            ]);
        }

        return $account->id;
    }

    /**
     * Format balance with Dr/Cr indicator
     */
    public static function formatBalance(float $balance): string
    {
        $formatted = number_format(abs($balance), 2);
        $suffix = $balance >= 0 ? 'Dr' : 'Cr';

        return "{$formatted} {$suffix}";
    }
}
