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
                <input type="text" class="form-control" name="customer_id" readonly value="{{ $customer->customer_id }}">
            </div>
            <div class="col-md-5">
                <label>Customer:</label>
                <input type="text" class="form-control" name="customer_name" value="{{ $customer->customer_name }}">
            </div>
            <div class="col-md-5">
                <label>کسٹمر کا نام:</label>
                <input type="text" class="form-control text-end" name="customer_name_ur" dir="rtl" value="{{ $customer->customer_name_ur }}">
            </div>
        </div>

        <!-- Copy the remaining input fields from the create form and use $customer->... instead of old() -->

        <div class="text-center mt-3">
            <button class="btn btn-primary" type="submit">Update Customer</button>
        </div>
    </form>
</div>
            </div>
        </div>
    </div>
@endsection
