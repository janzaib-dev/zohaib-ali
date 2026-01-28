@extends('admin_panel.layout.app')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5>➕ Add Warehouse Stock</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('warehouse_stocks.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Warehouse</label>
                    <select name="warehouse_id" class="form-control" required>
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Product</label>
                    <select name="product_id" class="form-control select2-ajax-products" required>
                        <option value="">Select Product</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" required>
                </div>
                {{--  <div class="mb-3">
                <label>Price</label>
                <input type="number" step="0.01" name="price" class="form-control">
            </div>  --}}
                <div class="mb-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Add Stock</button>
            </form>
        </div>
    </div>

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2-ajax-products').select2({
                placeholder: 'Search Product (Name / SKU / Barcode)',
                allowClear: true,
                ajax: {
                    url: '{{ route('products.ajax.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
            });
        });
    </script>
@endsection
@endsection
