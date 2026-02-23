<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountsHeadController extends Controller
{
    public function index()
    {
        $heads = \App\Models\AccountHead::all();
        $accounts = \App\Models\Account::with('head')->get();

        // Define the 5 critical accounts and check if they exist with a valid head
        $criticalDefs = [
            [
                'key' => 'cash',
                'title' => 'Cash in Hand',
                'code' => 'CASH',
                'type' => 'Debit',
                'nature' => 'Asset',
                'head' => 'Current Assets',
                'search' => ['title', 'like', '%Cash%'],
            ],
            [
                'key' => 'ar',
                'title' => 'Accounts Receivable',
                'code' => 'AR',
                'type' => 'Debit',
                'nature' => 'Asset',
                'head' => 'Current Assets',
                'search' => ['title', 'like', '%Receivable%'],
            ],
            [
                'key' => 'ap',
                'title' => 'Accounts Payable',
                'code' => 'AP',
                'type' => 'Credit',
                'nature' => 'Liability',
                'head' => 'Current Liabilities',
                'search' => ['title', 'like', '%Payable%'],
            ],
            [
                'key' => 'sales',
                'title' => 'Sales Revenue',
                'code' => 'SALES',
                'type' => 'Credit',
                'nature' => 'Income',
                'head' => 'Income',
                'search' => ['account_code', '=', 'SALES'],
            ],
            [
                'key' => 'purchase',
                'title' => 'Purchase Expense',
                'code' => 'PURCHASE',
                'type' => 'Debit',
                'nature' => 'Expense',
                'head' => 'Expenses',
                'search' => ['account_code', '=', 'PURCHASE'],
            ],
        ];

        $criticalCOA = collect($criticalDefs)->map(function ($def) {
            $existing = \App\Models\Account::where($def['search'][0], $def['search'][1], $def['search'][2])->first();
            $def['exists'] = (bool) $existing;
            $def['has_head'] = $existing && ! is_null($existing->head_id);
            $def['complete'] = $def['exists'] && $def['has_head'];
            $def['account'] = $existing;
            $def['head_name'] = $existing?->head?->name ?? null;

            return $def;
        });

        $anyMissing = $criticalCOA->contains('complete', false);

        return view('admin_panel.chart_of_accounts', compact('heads', 'accounts', 'criticalCOA', 'anyMissing'));
    }

    public function storeHead(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:account_heads,name',
        ]);

        \App\Models\AccountHead::create([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Account Head added successfully!');
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'head_id' => 'required|exists:account_heads,id',
            'title' => 'required',
            'opening_balance' => 'required|numeric',
            'type' => 'required',
        ]);

        // Generate Account Code (Simple auto-increment logic or similar)
        // For now, let's keep it simple or auto-generate if nullable.
        // Migration said account_code is nullable. I'll rely on ID or generate one.
        // Let's generate a basic one: ACC-{ID}

        $account = \App\Models\Account::create([
            'head_id' => $request->head_id,
            'title' => $request->title,
            'opening_balance' => $request->opening_balance, // This will serve as initial debit/credit context usually
            'type' => $request->type,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $account->account_code = 'ACC-'.str_pad($account->id, 4, '0', STR_PAD_LEFT);
        $account->save();

        return back()->with('success', 'Account added successfully!');
    }

    public function showLedger($id, Request $request)
    {
        $account = \App\Models\Account::findOrFail($id);

        // Fetch Journal Entries for this account
        $query = \App\Models\JournalEntry::where('account_id', $id)
            ->with('party') // Load party if polymorphic
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc');

        // Optional: Filter by Date Range
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('entry_date', [$request->from_date, $request->to_date]);
        }

        $entries = $query->get();

        return view('admin_panel.accounts.ledger', compact('account', 'entries'));
    }

    public function toggleStatus($id)
    {
        $account = \App\Models\Account::findOrFail($id);
        $account->status = ! $account->status;
        $account->save();

        return back()->with('success', 'Account status updated successfully!');
    }

    public function updateAccount(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Debit,Credit',
            'head_id' => 'required|exists:account_heads,id',
            'opening_balance' => 'required|numeric',
        ]);

        $account = \App\Models\Account::findOrFail($id);
        $account->title = $request->title;
        $account->type = $request->type;
        $account->head_id = $request->head_id;
        $account->opening_balance = $request->opening_balance;
        $account->save();

        return back()->with('success', "Account '{$account->title}' updated successfully!");
    }

    public function setupCOA(\Illuminate\Http\Request $request)
    {
        $keys = $request->input('keys', []);

        if (empty($keys)) {
            return back()->with('error', 'No accounts selected.');
        }

        $balanceService = app(\App\Services\BalanceService::class);

        // This triggers ensureDefaultCOA() for all heads + selected accounts
        // We call each relevant getter based on keys selected
        $keyMap = [
            'cash' => fn () => $balanceService->getCashAccountId(),
            'ar' => fn () => $balanceService->getAccountsReceivableId(),
            'ap' => fn () => $balanceService->getAccountsPayableId(),
            'sales' => fn () => $balanceService->getSalesRevenueId(),
            'purchase' => fn () => $balanceService->getPurchaseExpenseId(),
        ];

        $created = [];
        foreach ($keys as $key) {
            if (isset($keyMap[$key])) {
                ($keyMap[$key])();
                $created[] = $key;
            }
        }

        return back()->with('success', count($created).' critical account(s) have been set up successfully!');
    }
}
