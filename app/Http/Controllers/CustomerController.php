<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use App\Models\SalesOfficer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    // ////////////
    // 🔹 Load customers list by type
    public function saleindex(Request $request)
    {
        $type   = $request->type   ?? 'Main Customer';
        $search = $request->search ?? '';

        $query = Customer::where('customer_type', $type);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_id',   'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('customer_name')->get();

        return response()->json($customers);
    }

    // 🔹 Single customer detail
    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        $data = $customer->toArray();
        $data['previous_balance'] = $customer->previous_balance;
        $data['balance_range'] = $customer->balance_range ?? 0;

        // Map status to remarks if needed by frontend
        $data['remarks'] = $customer->status ?? '';

        return response()->json($data);
    }

    // //////////

    public function index()
    {
        $customers = Customer::latest()->get(); // no status filter

        // echo "<pre>";
        // print_r($customers);
        // echo "</pre>";
        // dd();
        return view('admin_panel.customers.index', compact('customers'));
    }

    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->save();

        return redirect()->back()->with('success', 'Customer status updated.');
    }

    // Add this in CustomerController
    public function getCustomerLedger($id)
    {
        $ledger = CustomerLedger::where('customer_id', $id)->latest()->first();

        return response()->json([
            'closing_balance' => $ledger->closing_balance,
        ]);
    }

    public function markInactive($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = 'inactive';
        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Customer marked as inactive.');
    }

    public function inactiveCustomers()
    {
        $customers = Customer::where('status', 'inactive')->latest()->get();

        return view('admin_panel.customers.inactive', compact('customers'));
    }

    public function create()
    {
        $latestId = 'CUST-'.str_pad(Customer::max('id') + 1, 4, '0', STR_PAD_LEFT);
        $salesOfficers = SalesOfficer::orderBy('name')->get();

        return view('admin_panel.customers.create', compact('latestId', 'salesOfficers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'      => 'required|unique:customers',
            'customer_name'    => 'nullable',
            'customer_name_ur' => 'nullable',
            'cnic'             => 'nullable',
            'filer_type'       => 'nullable',
            'zone'             => 'nullable',
            'contact_person'   => 'nullable',
            'mobile'           => 'nullable',
            'email_address'    => 'nullable|email',
            'contact_person_2' => 'nullable',
            'mobile_2'         => 'nullable',
            'email_address_2'  => 'nullable|email',
            'opening_balance'  => 'nullable|numeric',
            'balance_range'    => 'nullable|numeric',
            'address'          => 'nullable',
            'customer_type'    => 'nullable',
            'sales_officer_id' => 'nullable|exists:sales_officers,id',
        ]);

        // Customer create
        $customer = Customer::create($data);

        // Ledger me entry agar opening balance dia gaya ho
        $opening = $data['opening_balance'] ?? 0;

        if ($opening > 0) {
            CustomerLedger::create([
                'customer_id' => $customer->id,
                'admin_or_user_id' => Auth::id(),
                'previous_balance' => 0,
                'opening_balance' => $opening,           // ✅ yahan set karna zaroori hai
                'closing_balance' => $opening,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        return view('admin_panel.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $data = $request->except('_token');

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    // customer ledger start

    // Customer Ledger View
    public function customer_ledger(Request $request)
    {
        if (Auth::check()) {
            
            $customers = Customer::all();
            $ledgerData = [];
            
            if ($request->filled('customer_id')) {
                // Use Balance Service for accurate Statement
                $balanceService = app(\App\Services\BalanceService::class);
                
                $startDate = $request->from_date ?? '2000-01-01';
                $endDate = $request->to_date ?? date('Y-m-d');
                
                $data = $balanceService->getCustomerLedger($request->customer_id, $startDate, $endDate);
                
                // transform for view
                $ledgerData = collect($data['transactions'])->map(function($t) use ($data) {
                    return (object) [
                        'created_at' => \Carbon\Carbon::parse($t['date']),
                        'customer' => $data['customer'],
                        'description' => $t['description'],
                        'debit' => $t['debit'],
                        'credit' => $t['credit'],
                        'closing_balance' => $t['balance'],
                        // We act as if previous balance is calculated, but views usually use these explicitly now
                        'previous_balance' => $t['balance'] - ($t['debit'] - $t['credit']) 
                    ];
                });
                
                // Add Opening Balance as first row if needed?
                // The BalanceService includes opening balance in the calculation but returns transactions.
                // We might want to pass opening balance to view.
                
            } else {
                // If no customer selected, show empty or recent journal entries?
                // For now, let's keep it empty to encourage selection or just basic legacy entries if needed
                // But legacy entries are wrong. Let's return empty to force selection.
                $ledgerData = collect([]);
            }

            return view('admin_panel.customers.customer_ledger', [
                'CustomerLedgers' => $ledgerData,
                'customers' => $customers
            ]);
            
        } else {
            return redirect()->back();
        }
    }
    // customer payment start

    // View all customer payments
    public function customer_payments()
    {
        $payments = CustomerPayment::with('customer')->orderByDesc('id')->get();
        $customers = Customer::all();

        return view('admin_panel.customers.customer_payments', compact('payments', 'customers'));
    }

    // Store a customer payment
    public function store_customer_payment(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'adjustment_type' => 'required|in:plus,minus',
            'payment_method' => 'nullable|string',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $userId = Auth::id();

        // Save the payment
        CustomerPayment::create([
            'customer_id' => $request->customer_id,
            'admin_or_user_id' => $userId,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'note' => $request->note,
        ]);

        // Get latest ledger record to calculate new balance
        $latestLedger = CustomerLedger::where('customer_id', $request->customer_id)->latest()->first();

        // Default to opening balance if no ledger exists, or 0
        // If no ledger exists, we assume previous balance is opening balance of customer?
        // But checking 'customers' table again is safer.
        $previousBalance = 0;
        if ($latestLedger) {
            $previousBalance = $latestLedger->closing_balance;
        } else {
            $cust = Customer::find($request->customer_id);
            $previousBalance = $cust->opening_balance ?? 0;
        }

        // Calculate new balance
        $newBalance = $request->adjustment_type === 'plus'
            ? $previousBalance + $request->amount
            : $previousBalance - $request->amount;

        // Create NEW ledger record (Preserve History)
        CustomerLedger::create([
            'customer_id' => $request->customer_id,
            'admin_or_user_id' => $userId,
            'previous_balance' => $previousBalance,
            'opening_balance' => 0, // This is not an "opening" entry, so 0 or null
            'closing_balance' => $newBalance,
            'description' => 'Payment: '.($request->note ?? $request->payment_method),
        ]);

        return back()->with('success', 'Payment adjusted and ledger updated.');
    }

    public function destroy_payment($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        $customerId = $payment->customer_id;
        $amount = $payment->amount;

        // Latest ledger record for that customer
        $ledger = CustomerLedger::where('customer_id', $customerId)
            ->orderBy('id', 'desc')
            ->first();
        if ($ledger) {
            $ledger->closing_balance += $amount;
            $ledger->save();
        }

        // Delete the payment entry
        $payment->delete();

        return redirect()->back()->with('success', 'Payment deleted and customer ledger updated successfully.');
    }

    public function getByType(Request $request)
    {
        $type = $request->get('type');

        $customers = Customer::where('customer_type', $type)->get(['id', 'customer_name']);

        return response()->json(['customers' => $customers]);
    }
}
