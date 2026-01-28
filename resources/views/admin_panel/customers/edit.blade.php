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
                            <select class="form-control" name="zone">
                                <option value="Hyderabad" {{ $customer->zone == 'Hyderabad' ? 'selected' : '' }}>Hyderabad
                                </option>
                                <option value="Karachi" {{ $customer->zone == 'Karachi' ? 'selected' : '' }}>Karachi
                                </option>
                            </select>
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
@endsection
