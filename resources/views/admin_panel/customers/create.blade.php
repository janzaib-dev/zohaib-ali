@extends('admin_panel.layout.app')

@section('content')
    <style>
        /* Modern Compact UI variables */
        @import url('https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400..700&display=swap');

        :root {
            --primary-color: #4f46e5;
            /* Indigo */
            --bg-light: #f8fafc;
            --input-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-muted: #64748b;
            --text-dark: #1e293b;
        }

        /* Main Container Cleanup */
        .main-content {
            overflow: hidden;
            /* Enforce no scroll */
        }

        .main-content-inner {
            padding: 10px;
        }

        /* Modern Card */
        .modern-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            padding: 20px;
            height: calc(100vh - 140px);
            /* Fill remaining space */
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-title i {
            color: var(--primary-color);
            background: #e0e7ff;
            padding: 8px;
            border-radius: 8px;
            font-size: 1rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 12px 16px;
            overflow-y: auto;
            /* Internal scroll if absolutely needed on tiny screens */
            padding-right: 5px;
            /* space for scrollbar */
        }

        /* Modern Inputs */
        .input-group-modern {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .modern-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }

        .modern-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            color: var(--text-dark);
            transition: all 0.2s ease;
            width: 100%;
        }

        .modern-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
            background: white;
        }

        .modern-control::placeholder {
            color: #cbd5e1;
        }

        select.modern-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        textarea.modern-control {
            resize: none;
            min-height: 38px;
        }

        /* Sections */
        .section-label {
            grid-column: span 12;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-top: 8px;
            margin-bottom: 0px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        /* Buttons */
        .btn-modern-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(79, 70, 229, 0.3);
        }

        .btn-modern-primary:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.4);
        }

        .btn-modern-secondary {
            background: #f1f5f9;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-modern-secondary:hover {
            background: #e2e8f0;
        }

        /* Scrollbar Styling */
        .form-grid::-webkit-scrollbar {
            width: 6px;
        }

        .form-grid::-webkit-scrollbar-track {
            background: transparent;
        }

        .form-grid::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }

        .pl-icon {
            padding-left: 2.5rem !important;
        }

        .modern-control[readonly] {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid p-0">

                <form action="{{ route('customers.store') }}" method="POST" class="needs-validation modern-card"
                    novalidate>
                    @csrf

                    <!-- Header -->
                    <div class="page-header">
                        <h1 class="page-title">
                            <i class="fa fa-user-plus"></i> New Customer
                        </h1>
                        <div class="d-flex gap-2">
                            <a href="{{ route('customers.index') }}" class="btn-modern-secondary">
                                <i class="fa fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn-modern-primary">
                                <i class="fa fa-check me-1"></i> Save Customer
                            </button>
                        </div>
                    </div>

                    <!-- Form Content Grid -->
                    <div class="form-grid">

                        <!-- Section 1 -->
                        <div class="section-label mt-0"><i class="fas fa-user-circle me-2"></i>Personal & Account
                            Information</div>

                        <div class="input-group-modern" style="grid-column: span 4;">
                            <label class="modern-label">Customer Code</label>
                            <input type="text" class="modern-control" name="customer_id" value="{{ $latestId }}"
                                readonly>
                        </div>
                        <div class="input-group-modern" style="grid-column: span 4;">
                            <label class="modern-label">Customer Type <span class="text-danger">*</span></label>
                            <select class="modern-control" name="customer_type" required>
                                <option value="Main Customer">Main Customer</option>
                                <option value="Walking Customer">Walking Customer</option>
                            </select>
                        </div>
                        <div class="input-group-modern" style="grid-column: span 4;">
                            <label class="modern-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="modern-control" name="customer_name" required
                                placeholder="Customer Name">
                        </div>

                        <!-- Section 2 -->
                        <div class="section-label"><i class="fas fa-address-book me-2"></i>Contact Details</div>

                        <div class="input-group-modern" style="grid-column: span 4;">
                            <label class="modern-label">Mobile</label>
                            <div class="position-relative">
                                <i class="fas fa-phone position-absolute text-muted"
                                    style="top: 50%; left: 12px; transform: translateY(-50%);"></i>
                                <input type="text" class="modern-control pl-icon" name="mobile"
                                    placeholder="0300-1234567">
                            </div>
                        </div>

                        <div class="input-group-modern" style="grid-column: span 4;">
                            <label class="modern-label">Email Address</label>
                            <div class="position-relative">
                                <i class="fas fa-envelope position-absolute text-muted"
                                    style="top: 50%; left: 12px; transform: translateY(-50%);"></i>
                                <input type="email" class="modern-control pl-icon" name="email_address"
                                    placeholder="info@example.com">
                            </div>
                        </div>
                        <div class="input-group-modern" style="grid-column: span 4;">
                            <label class="modern-label">Sales Officer</label>
                            <select class="modern-control" name="sales_officer_id">
                                <option value="">-- Select Officer --</option>
                                @foreach ($salesOfficers as $officer)
                                    <option value="{{ $officer->id }}">{{ $officer->name }}
                                        @if ($officer->mobile)
                                            ({{ $officer->mobile }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Section 3 -->
                        <div class="section-label"><i class="fas fa-map-marker-alt me-2"></i>Region & Location</div>

                        <div class="input-group-modern" style="grid-column: span 4;">
                            <label class="modern-label">Region (Zone)</label>
                            <div class="d-flex w-100 gap-1" style="align-items: stretch;">
                                <select class="modern-control" name="zone" id="zone_select" style="flex: 1;">
                                    <option value="">-- Select Zone --</option>
                                    @foreach ($zones as $z)
                                        <option value="{{ $z->zone }}">{{ $z->zone }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="btn btn-primary d-flex align-items-center justify-content-center border-0 shadow-sm"
                                    style="border-radius: 8px; padding: 0 15px; margin-left: 5px; background: var(--primary-color);"
                                    onclick="$('#zoneModal').modal('show')">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Address spans full width -->
                        <div class="input-group-modern" style="grid-column: span 8;">
                            <label class="modern-label">Billing Address</label>
                            <div class="position-relative">
                                <i class="fas fa-home position-absolute text-muted"
                                    style="top: 50%; left: 12px; transform: translateY(-50%);"></i>
                                <input type="text" class="modern-control pl-icon" name="address"
                                    placeholder="Shop No, Street Area, City">
                            </div>
                        </div>

                        <!-- Section 4 -->
                        <div class="section-label"><i class="fas fa-file-invoice-dollar me-2"></i>Tax & Financials</div>

                        <div class="input-group-modern" style="grid-column: span 3;">
                            <label class="modern-label">CNIC / NTN</label>
                            <div class="position-relative">
                                <i class="fas fa-id-card position-absolute text-muted"
                                    style="top: 50%; left: 12px; transform: translateY(-50%);"></i>
                                <input type="text" class="modern-control pl-icon" name="cnic"
                                    placeholder="42201-...">
                            </div>
                        </div>
                        <div class="input-group-modern" style="grid-column: span 3;">
                            <label class="modern-label">Tax Status</label>
                            <select class="modern-control" name="filer_type">
                                <option value="filer">Active Filer</option>
                                <option value="non filer" selected>Non Filer</option>
                                <option value="exempt">Tax Exempt</option>
                            </select>
                        </div>
                        <div class="input-group-modern" style="grid-column: span 3;">
                            <label class="modern-label text-danger">Opening Balance (Dr)</label>
                            <div class="position-relative">
                                <span class="position-absolute fw-bold text-danger"
                                    style="top: 50%; left: 12px; transform: translateY(-50%); border-right: 1px solid #fca5a5; padding-right: 8px;">Rs</span>
                                <input type="number" step="0.01" class="modern-control" name="opening_balance"
                                    value="0"
                                    style="padding-left: 3rem; border-color: #fca5a5; background: #fff1f2; font-weight: bold; color: #dc2626;">
                            </div>
                        </div>
                        <div class="input-group-modern" style="grid-column: span 3;">
                            <label class="modern-label text-success">Credit Limit</label>
                            <div class="position-relative">
                                <span class="position-absolute fw-bold text-success"
                                    style="top: 50%; left: 12px; transform: translateY(-50%); border-right: 1px solid #86efac; padding-right: 8px;">Rs</span>
                                <input type="number" step="0.01" class="modern-control" name="balance_range"
                                    value="0"
                                    style="padding-left: 3rem; border-color: #86efac; background: #f0fdf4; font-weight: bold; color: #16a34a;">
                            </div>
                        </div>

                    </div>
                    <!-- End Grid -->

                </form>
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
                        <div class="form-group border-0">
                            <label class="modern-label mb-2">Zone Name</label>
                            <input type="text" name="zone" class="modern-control" id="new_zone_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modern-secondary" data-bs-dismiss="modal"
                            onclick="$('#zoneModal').modal('hide')">Close</button>
                        <button type="submit" class="btn-modern-primary">Save Zone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Aesthetic focus
            let nameField = document.querySelector('input[name="customer_name"]');
            if (nameField) nameField.focus();

            // Confirmation on Submit
            const form = document.querySelector('form.needs-validation');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Stop initial submit

                    // Basic validation check (Bootstrap)
                    if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        // Find first invalid
                        let invalid = form.querySelector(':invalid');
                        if (invalid) invalid.focus();
                        return;
                    }

                    // SweetAlert Confirmation
                    Swal.fire({
                        title: 'Confirm Save',
                        text: "Are you sure you want to save this customer?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Save it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Manually submit if confirmed
                        }
                    });
                });
            }

            // Zone Addition via Ajax
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
                            Swal.fire('Success', 'Zone added successfully!', 'success');
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', "Error adding zone. Make sure it isn't empty.",
                            'error');
                        console.error(err);
                    }
                });
            });
        });
    </script>
@endsection
