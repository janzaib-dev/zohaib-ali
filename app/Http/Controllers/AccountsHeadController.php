<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountsHeadController extends Controller
{
    public function index()
    {
        $heads = \App\Models\AccountHead::all();
        $accounts = \App\Models\Account::with('head')->get();
        return view('admin_panel.chart_of_accounts', compact('heads', 'accounts'));
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
            'type' => 'required'
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

        $account->account_code = 'ACC-' . str_pad($account->id, 4, '0', STR_PAD_LEFT);
        $account->save();

        return back()->with('success', 'Account added successfully!');
    }
}
