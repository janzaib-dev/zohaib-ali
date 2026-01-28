@extends('admin_panel.layout.app')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>📦 Warehouse Stock List</h5>
            @can('warehouse.stock.create')
                <a href="{{ route('warehouse_stocks.create') }}" class="btn btn-primary btn-sm">Add Stock</a>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="stockTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Warehouse</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        {{--  <th>Price</th>  --}}
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stocks as $stock)
                        <tr>
                            <td>{{ $stocks->firstItem() + $loop->index }}</td>
                            <td>{{ $stock->warehouse->warehouse_name }}</td>
                            <td>{{ $stock->product->item_name }}</td>
                            <td>{{ $stock->quantity }}</td>
                            {{--  <td>{{ $stock->price }}</td>  --}}
                            <td>{{ $stock->remarks }}</td>
                            <td>
                                @can('warehouse.stock.edit')
                                    <a href="{{ route('warehouse_stocks.edit', $stock->id) }}"
                                        class="btn btn-sm btn-warning">Edit</a>
                                @endcan
                                <form action="{{ route('warehouse_stocks.destroy', $stock->id) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    @can('warehouse.stock.delete')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    @endcan
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $stocks->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@section('js')
    {{-- <script>
        $(document).ready(function() {
            $('#stockTable').DataTable();
        });
    </script> --}}
@endsection
