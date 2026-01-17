@extends('admin_panel.layout.app')

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">

                <div class="page-header row">
                    <div class="page-title col-lg-6">
                        <h4>Vendor List</h4>
                    </div>
                    <div class="page-btn d-flex justify-content-end col-lg-6">
                        @can('vendors.create')
                            <button class="btn btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#vendorModal"
                                onclick="clearVendor()">Add Vendor</button>
                        @endcan
                        <a href="{{ url('vendors-ledger') }}" class="btn btn-sm btn-danger ms-2 mb-2">Ledger</a>
                        <a href="{{ route('vendor.payments') }}" class="btn btn-sm btn-danger ms-2 mb-2">Payments</a>
                        <a href="{{ url('vendor/bilties') }}" class="btn btn-sm btn-danger ms-2 mb-2">Bilty</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success"><strong>Success!</strong> {{ session('success') }}</div>
                        @endif

                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Opening Balance</th>
                                    <th>Address</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendors as $key => $v)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $v->name }}</td>
                                        <td>{{ $v->phone }}</td>
                                        <td>{{ $v->opening_balance }}</td>
                                        <td>{{ $v->address }}</td>
                                        <td>
                                            @include('admin_panel.partials.action_buttons', [
                                                'editRoute' => 'javascript:void(0)',
                                                'deleteRoute' => url('vendor/delete/' . $v->id),
                                                'editIsLink' => false,
                                                'permissions' => [
                                                    'edit' => 'vendors.edit',
                                                    'delete' => 'vendors.delete',
                                                ],
                                                'dataId' => $v->id,
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Vendor -->
    <div class="modal fade" id="vendorModal">
        <div class="modal-dialog">
            <form action="{{ url('vendor/store') }}" method="POST">@csrf
                <input type="hidden" id="vendor_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add/Edit Vendor</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <input class="form-control" name="name" id="vname" placeholder="Name" required>
                        </div>
                        <div class="mb-2">
                            <input class="form-control" name="opening_balance" id="opening_balance"
                                placeholder="Opening Balance" required>
                        </div>
                        <div class="mb-2">
                            <input class="form-control" name="phone" id="vphone" placeholder="Phone">
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" name="address" id="vaddress" placeholder="Address"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @can('vendors.create')
                            <button class="btn btn-primary">Save</button>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('.datanew').DataTable();

        // Clear modal fields function
        window.clearVendor = function() {
            $('#vendor_id').val('');
            $('#vname').val('');
            $('#opening_balance').val('').prop('readonly', false); // Allow editing
            $('#vphone').val('');
            $('#vaddress').val('');
        };

        // Edit Vendor functionality
        // Use delegated handler in case buttons are rendered by a partial/component
        $(document).on('click', '.edit-btn', function() {
            var $btn = $(this);
            var row = $btn.closest('tr');
            var id = $btn.data('id');
            var name = row.find('td:eq(1)').text().trim();
            var phone = row.find('td:eq(2)').text().trim();
            var balance = row.find('td:eq(3)').text().trim();
            var address = row.find('td:eq(4)').text().trim();

            // Populate modal with vendor data
            $('#vendor_id').val(id);
            $('#vname').val(name);
            $('#vphone').val(phone);
            $('#opening_balance').val(balance).prop('readonly',
            true); // Prevent editing opening balance
            $('#vaddress').val(address);

            var modal = new bootstrap.Modal(document.getElementById('vendorModal'));
            modal.show(); // Show the modal
        });

        // Delete fallback: if confirmedBox is not defined globally, provide a simple confirm
        $(document).on('click', '.delete-btn', function(e) {
            var el = this;
            if (typeof window.confirmedBox === 'function') {
                // Let existing handler (in partial) call confirmedBox via onclick attribute
                return;
            }
            e.preventDefault();
            var msg = $(this).data('msg') || 'Are you sure?';
            var href = $(this).attr('href');
            if (confirm(msg)) {
                window.location.href = href;
            }
        });
    });
</script>
