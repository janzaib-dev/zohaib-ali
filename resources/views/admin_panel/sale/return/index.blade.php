@extends('admin_panel.layout.app')
@section('content')

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sale Returns</h5>
            <a href="{{ route('sale.add') }}" class="btn btn-primary btn-sm">Add Sale</a>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Total Items</th>
                        <th>Total Net</th>
                        <th>Return Note</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesReturns as $return)
                    <tr>
                        <td>{{ $return->id }}</td>
                        <td>{{ $return->sale->product_code ?? 'N/A' }}</td>
                        <td>{{ $return->sale->customer ?? $return->customer }}</td>
                        <td>{{ $return->total_items }}</td>
                        <td>{{ number_format($return->total_net, 2) }}</td>
                        <td>{{ $return->return_note }}</td>
                        <td>{{ $return->created_at->format('d-m-Y') }}</td>
<td>
                            <span class="badge bg-danger">Return</span>
                        </td>

                        
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
