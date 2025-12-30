@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Customer Ledger</h4>
                    <h6>View ledger by date range</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form id="ledgerForm" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="loader" style="display:none;text-align:center;margin-bottom:10px;">
                        <div class="spinner-border" role="status"></div>
                    </div>

                    <div id="ledgerBox" style="display:none;">
                        <div class="ledger-box">
                            <div class="ledger-title">CUSTOMER LEDGER</div>
                            <div id="ledgerHeader" class="ledger-header mb-3"></div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="text-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice</th>
                                            <th>Description</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ledgerBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
<style>
    .ledger-box {
        border: 1px solid #333;
        padding: 15px;
        margin: 20px auto;
        width: 100%;
        background: #fff;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    }

    .ledger-title {
        text-align: center;
        font-weight: 700;
        font-size: 20px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #222;
    }

    .ledger-header {
        padding: 8px 10px;
        border: 1px solid #333;
        margin-bottom: 15px;
        background: #f8f9fa;
        font-size: 14px;
    }

    .ledger-header strong {
        font-weight: 600;
    }

    table {
        font-size: 14px;
        border: 1px solid #333;
        border-collapse: collapse;
        width: 100%;
    }

    table thead th {
        background: #444;
        color: #000;
        font-weight: 600;
        border: 1px solid #333;
        text-align: center;
        padding: 8px;
    }

    table tbody td {
        border: 1px solid #333;
        text-align: center;
        padding: 7px;
    }

    .text-left {
        text-align: left !important;
    }

    .opening-balance {
        font-weight: 600;
    }

    .balance-positive {
        color: #198754;
        /* green */
        font-weight: 600;
    }

    .balance-negative {
        color: #dc3545;
        /* red */
        font-weight: 600;
    }

    .balance-neutral {
        color: #0d6efd;
        /* blue */
        font-weight: 600;
    }

    .totals-row td {
        font-weight: 700;
        background: #e9ecef;
        border-top: 2px solid #333;
    }
</style>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '#btnSearch', function() {

            let cid = $("#customer_id").val();
            let start = $("#start_date").val();
            let end = $("#end_date").val();
            if (!cid || !start || !end) {
                alert("Select all fields");
                return;
            }

            $("#loader").show();
            $.get("{{ route('report.customer.ledger.fetch') }}", {
                customer_id: cid,
                start_date: start,
                end_date: end
            }, function(res) {
                $("#loader").hide();
                $("#ledgerBox").show();

                $("#ledgerHeader").html(`
                    <strong>Customer:</strong> ${res.customer.customer_name}
                    <span style="float:right;">
                        <strong>Duration:</strong> From ${start} To ${end}
                    </span>
                `);

                let totalDebit = 0;
                let totalCredit = 0;
                let lastBalance = res.opening_balance;

                // Opening Balance Row
                let html = `
    <tr>
        <td>N/A</td>
        <td>-</td>
        <td class="text-left opening-balance">Opening Balance</td>
        <td>-</td>
        <td>-</td>
        <td class="balance-neutral">Rs. ${parseFloat(res.opening_balance).toFixed(2)}</td>
    </tr>
`;

                res.transactions.forEach((t) => {
                    let debit = t.debit && t.debit > 0 ? parseFloat(t.debit) : 0;
                    let credit = t.credit && t.credit > 0 ? parseFloat(t.credit) : 0;
                    totalDebit += debit;
                    totalCredit += credit;
                    lastBalance = parseFloat(t.balance);

                    html += `
        <tr>
            <td>${t.date.split(" ")[0]}</td>
            <td>${t.invoice ?? '-'}</td>
            <td class="text-left">${t.description}</td>
            <td>${debit > 0 ? 'Rs. ' + debit.toFixed(2) : '-'}</td>
            <td>${credit > 0 ? 'Rs. ' + credit.toFixed(2) : '-'}</td>
            <td class="${lastBalance > 0 ? 'balance-positive' : (lastBalance < 0 ? 'balance-negative' : 'balance-neutral')}">
                Rs. ${lastBalance.toFixed(2)}
            </td>
        </tr>
    `;
                });

                // Totals Row
                html += `
    <tr class="totals-row">
        <td colspan="3" class="text-left">Totals:</td>
        <td>Rs. ${totalDebit.toFixed(2)}</td>
        <td>Rs. ${totalCredit.toFixed(2)}</td>
        <td class="${lastBalance > 0 ? 'balance-positive' : (lastBalance < 0 ? 'balance-negative' : 'balance-neutral')}">
            Rs. ${lastBalance.toFixed(2)}
        </td>
    </tr>
`;
                $("#ledgerBody").html(html);
            });
        });
    });
</script>