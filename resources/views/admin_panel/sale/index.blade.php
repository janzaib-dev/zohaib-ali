@extends('admin_panel.layout.app')
@section('content')

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0">SALES</h5>
            <div>
                <span class="fw-bold text-dark"><a href="{{ route('sale.add') }}" class="btn btn-primary">Add sale</a></span>
                <span class="fw-bold text-dark"><a href="{{ url('bookings') }}" class="btn btn-primary">All Booking</a></span>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>discount</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status Sale</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->id }}</td>
                        <td>{{ $sale->customer_relation->customer_name ?? 'N/A' }}</td>
                        <td>{{ $sale->reference }}</td>
                        <td>{{ $sale->product_relation->item_name ?? 'N/A' }}</td>
                        <td>{{ $sale->qty }}</td>
                        <td>{{ $sale->per_price }}</td>
                        <td>{{ $sale->per_discount}}</td>
                        <td>{{ $sale->per_total }}</td>
                        <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                        <td>
                            @if($sale->sale_status === null)
                            <span class="badge bg-success">Sale</span>
                            @elseif($sale->sale_status == 1)
                            <span class="badge bg-danger">Return</span>
                            @else
                            <span class="badge bg-secondary">Unknown</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Actions">
                                <a href="{{ route('sales.return.create', $sale->id) }}" class="btn btn-sm btn-warning">
                                    Return
                                </a>
                                <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-primary">
                                    Edit
                                </a>
                                <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-sm btn-info text-white">Invoice</a>
                                <a href="{{ route('sales.recepit', $sale->id) }}" class="btn btn-sm btn-danger text-white">Recepit</a>
                                <a href="{{ route('sales.dc', $sale->id) }}" class="btn btn-sm btn-success text-white">DC</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    </div>
</div>

@endsection