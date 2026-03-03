@extends('admin_panel.layout.app')
@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <h3>Edit Customer</h3>
                <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                    @csrf
                    <!-- No PUT, since we're using POST only -->

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label>Customer ID:</label>
                            <input type="text" class="form-control" name="customer_id" readonly
                                value="{{ $customer->customer_id }}">
                        </div>
                        <div class="col-md-5">
                            <label>Customer:</label>
                            <input type="text" class="form-control" name="customer_name"
                                value="{{ $customer->customer_name }}">
                        </div>
                        <div class="col-md-5">
                            <label>کسٹمر کا نام:</label>
                            <input type="text" class="form-control text-end" name="customer_name_ur" dir="rtl"
                                value="{{ $customer->customer_name_ur }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>NTN / CNIC no:</label>
                            <input type="text" class="form-control" name="cnic" value="{{ $customer->cnic }}">
                        </div>
                        <div class="col-md-4">
                            <label>Filer Type:</label>
                            <select class="form-control" name="filer_type">
                                <option value="filer" {{ $customer->filer_type == 'filer' ? 'selected' : '' }}>Filer
                                </option>
                                <option value="non filer" {{ $customer->filer_type == 'non filer' ? 'selected' : '' }}>Non
                                    Filer</option>
                                <option value="exempt" {{ $customer->filer_type == 'exempt' ? 'selected' : '' }}>Exempt
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Zone:</label>
                            <div class="d-flex w-100 gap-1">
                                <select class="form-control" name="zone" id="zone_select" style="flex: 1;">
                                    <option value="">-- Select Zone --</option>
                                    @foreach ($zones as $z)
                                        <option value="{{ $z->zone }}"
                                            {{ $customer->zone == $z->zone ? 'selected' : '' }}>{{ $z->zone }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="btn btn-primary d-flex align-items-center justify-content-center"
                                    style="margin-left: 5px;" onclick="$('#zoneModal').modal('show')">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Contact Person:</label>
                            <input type="text" class="form-control" name="contact_person"
                                value="{{ $customer->contact_person }}">
                        </div>
                        <div class="col-md-4">
                            <label>Mobile#:</label>
                            <input type="text" class="form-control" name="mobile" value="{{ $customer->mobile }}">
                        </div>
                        <div class="col-md-4">
                            <label>Email Address:</label>
                            <input type="email" class="form-control" name="email_address"
                                value="{{ $customer->email_address }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Contact Person-2:</label>
                            <input type="text" class="form-control" name="contact_person_2"
                                value="{{ $customer->contact_person_2 }}">
                        </div>
                        <div class="col-md-4">
                            <label>Mobile# 2:</label>
                            <input type="text" class="form-control" name="mobile_2" value="{{ $customer->mobile_2 }}">
                        </div>
                        <div class="col-md-4">
                            <label>Email Address 2:</label>
                            <input type="email" class="form-control" name="email_address_2"
                                value="{{ $customer->email_address_2 }}">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label>Opening Balance (Dr):</label>
                            <input type="number" step="0.01" class="form-control" name="opening_balance"
                                value="{{ $customer->opening_balance ?? 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label>Balance Range (Credit Limit):</label>
                            <input type="number" step="0.01" class="form-control" name="balance_range"
                                value="{{ $customer->balance_range ?? 0 }}">
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label>Address:</label>
                        <textarea rows="4" class="form-control" name="address">{{ $customer->address }}</textarea>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-primary" type="submit">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- Zone Modal -->
    <div class="modal fade" id="zoneModal" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="zoneForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="zoneModalLabel">Add New Zone</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            onclick="$('#zoneModal').modal('hide')"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Zone Name</label>
                            <input type="text" name="zone" class="form-control" id="new_zone_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            onclick="$('#zoneModal').modal('hide')">Close</button>
                        <button type="submit" class="btn btn-primary">Save Zone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#zoneForm').submit(function(e) {
                e.preventDefault();
                let newZone = $('#new_zone_name').val();
                if (!newZone) return;

                $.ajax({
                    url: "{{ route('zone.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            // Append new zone to select and select it
                            $('#zone_select').append('<option value="' + newZone +
                                '" selected>' + newZone + '</option>');
                            $('#zoneModal').modal('hide');
                            $('#zoneForm')[0].reset();
                            alert("Zone added successfully!");
                        }
                    },
                    error: function(err) {
                        alert("Error adding zone. Make sure it isn't empty.");
                        console.error(err);
                    }
                });
            });
        });
    </script>
@endsection
