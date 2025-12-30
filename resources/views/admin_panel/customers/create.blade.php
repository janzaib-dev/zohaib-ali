@extends('admin_panel.layout.app')
@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
  <h3>Add New Customer</h3>
    <form action="{{ route('customers.store') }}" method="POST">
        @csrf

      

  <div class="row mb-3">
                            <div class="col-md-3">
                                <label><strong>Customer ID:</strong></label>
                                <input type="text" class="form-control" name="customer_id" readonly value="{{ $latestId }}">
                            </div>
                                           <div class="col-md-3">
                                <label><strong>Customer Type :</strong></label>
                                <select class="form-control" name="customer_type">
                                    <option>Main Customer</option>
                                    <option>Walking Customer</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label><strong>Customer:</strong></label>
                                <input type="text" class="form-control" name="customer_name" value="{{ old('customer_name') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="float-end"><strong>کسٹمر کا نام:</strong></label>
                                <input type="text" class="form-control text-end" dir="rtl" name="customer_name_ur" value="{{ old('customer_name_ur') }}">
                            </div>
                
                        </div>


        <div class="row mb-3">
            <div class="col-md-4">
                <label>NTN / CNIC no:</label>
                <input type="text" class="form-control" name="cnic" value="{{ old('cnic') }}">
            </div>
            <div class="col-md-4">
                <label>Filer Type:</label>
                <select class="form-control" name="filer_type">
                    <option value="filer">Filer</option>
                    <option value="non filer">Non Filer</option>
                    <option value="exempt">Exempt</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Zone:</label>
                <select class="form-control" name="zone">
                    <option value="Hyderabad">Hyderabad</option>
                    <option value="Karachi">Karachi</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Contact Person:</label>
                <input type="text" class="form-control" name="contact_person" value="{{ old('contact_person') }}">
            </div>
            <div class="col-md-4">
                <label>Mobile#:</label>
                <input type="text" class="form-control" name="mobile" value="{{ old('mobile') }}">
            </div>
            <div class="col-md-4">
                <label>Email Address:</label>
                <input type="email" class="form-control" name="email_address" value="{{ old('email_address') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Contact Person-2:</label>
                <input type="text" class="form-control" name="contact_person_2" value="{{ old('contact_person_2') }}">
            </div>
            <div class="col-md-4">
                <label>Mobile# 2:</label>
                <input type="text" class="form-control" name="mobile_2" value="{{ old('mobile_2') }}">
            </div>
            <div class="col-md-4">
                <label>Email Address 2:</label>
                <input type="email" class="form-control" name="email_address_2" value="{{ old('email_address_2') }}">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label>opening balance (Dr):</label>
                <input type="number" class="form-control" name="opening_balance" value="{{ old('opening_balance') }}">
            </div>
            {{--  <div class="col-md-6">
                <label>Credit (Cr):</label>
                <input type="number" class="form-control" name="credit" value="{{ old('credit') }}">
            </div>  --}}
        </div>

        <div class="col-md-6 mb-4">
            <label>Address:</label>
            <textarea rows="4" class="form-control" name="address">{{ old('address') }}</textarea>
        </div>

        <div class="text-center">
            <button class="btn btn-success" type="submit">Save Customer</button>
        </div>
    </form>
</div>
            </div>
        </div>
    </div>
@endsection
