@extends('admin_panel.layout.app')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5>➕ New Stock Transfer</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('stock_transfers.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>From Warehouse</label>
                    <select name="from_warehouse_id" id="from_warehouse_id" class="form-control" required>
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <div class="row">
                        <div class="col-lg-6">
                        <label>To</label>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-check-label" for="toShop">Transfer to Shop</label>

                        </div>

                        <div class="col-6">
                            <select name="to_warehouse_id" class="form-control">
                                <option value="">Select Warehouse</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <input class="form-check-input form-control" type="checkbox" name="to_shop" value="1"
                                id="toShop">


                        </div>
                    </div>
                </div>

                <table class="w-100 border text-center" id="product_table">
                    <thead>
                        <tr class="bg-light">
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="product_body">
                        <tr class="product_row">
                            <td>
                                <select name="product_id[]" class="form-control product-select" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->item_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="available_stock[]" class="form-control stock" readonly>
                            </td>
                            <td>
                                <input type="number" name="quantity[]" class="form-control quantity" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove-row">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Transfer Stock</button>
            </form>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function fetchStock() {
            var warehouseId = $('#from_warehouse_id').val();
            var productId = $('#product_id').val();

            if (warehouseId && productId) {
                $.ajax({
                    url: '/warehouse-stock-quantity',
                    method: 'GET',
                    data: {
                        warehouse_id: warehouseId,
                        product_id: productId
                    },
                    success: function(response) {
                        $('#available_stock').val(response.quantity);
                        $('#transfer_quantity').attr('max', response.quantity);
                    }
                });
            }
        }

        $('#from_warehouse_id, #product_id').change(fetchStock);

        $('#transfer_quantity').on('input', function() {
            var entered = parseInt($(this).val());
            var max = parseInt($(this).attr('max'));

            if (entered > max) {
                alert('Cannot transfer more than available stock!');
                $(this).val(max);
            }
        });
    });
</script>
<script>
    $(document).ready(function() {

        // ✅ Add new row automatically when product is selected
        $(document).on('change', '.product-select', function() {
            var currentRow = $(this).closest('tr');
            var selectedProduct = $(this).val();
            var fromWarehouse = $('#from_warehouse_id').val();

            if (selectedProduct && fromWarehouse) {
                $.ajax({
                    url: '/warehouse-stock-quantity',
                    method: 'GET',
                    data: {
                        warehouse_id: fromWarehouse,
                        product_id: selectedProduct
                    },
                    success: function(response) {
                        currentRow.find('.stock').val(response.quantity);
                        currentRow.find('.quantity').attr('max', response.quantity);
                    }
                });
            }

            // ✅ If last row, add new empty row
            if ($('#product_body tr:last').is(currentRow)) {
                addNewRow();
            }
        });

        // ✅ Auto validate quantity with stock
        $(document).on('input', '.quantity', function() {
            var entered = parseInt($(this).val());
            var max = parseInt($(this).attr('max'));

            if (entered > max) {
                alert('Cannot transfer more than available stock!');
                $(this).val(max);
            }
        });

        // ✅ Remove Row
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // ✅ Add new blank row
        function addNewRow() {
            var row = `
                <tr class="product_row">
                    <td>
                        <select name="product_id[]" class="form-control product-select" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->item_name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="available_stock[]" class="form-control stock" readonly>
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control quantity" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-row">Remove</button>
                    </td>
                </tr>
            `;
            $('#product_body').append(row);
        }
    });
</script>
