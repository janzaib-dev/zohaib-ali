@extends('admin_panel.layout.app')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5>✏️ Edit Warehouse Stock</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('warehouse_stocks.update', $warehouseStock->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label>Warehouse</label>
                    <select name="warehouse_id" class="form-control" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                {{ $warehouseStock->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->warehouse_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Product (Search to change)</label>
                    <select name="product_id" class="form-control select2-ajax-products" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}"
                                {{ $warehouseStock->product_id == $product->id ? 'selected' : '' }}>
                                {{ $product->item_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" name="quantity" value="{{ $warehouseStock->quantity }}" class="form-control"
                        required>
                </div>
                {{--  <div class="mb-3">
                <label>Price</label>
                <input type="number" step="0.01" name="price" value="{{ $warehouseStock->price }}" class="form-control">
            </div>  --}}
                <div class="mb-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control">{{ $warehouseStock->remarks }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Stock</button>
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
