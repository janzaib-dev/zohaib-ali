@extends('admin_panel.layout.app')

@section('content')
    @include('hr.partials.hr-styles')

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="page-title"><i class="fa fa-users"></i> Employee Management</h1>
                        <p class="page-subtitle">Manage your organization's employee database</p>
                    </div>
                    @can('hr.employees.create')
                        <button type="button" class="btn btn-create" id="createBtn">
                            <i class="fa fa-user-plus"></i> Add Employee
                        </button>
                    @endcan
                </div>

                <!-- Stats Row -->
                @php
                    $activeCount = $employees->where('status', 'active')->count();
                    $nonActiveCount = $employees->where('status', 'non-active')->count();
                    $terminatedCount = $employees->where('status', 'terminated')->count();
                @endphp
                <div class="stats-row">
                    <div class="stat-card primary">
                        <div class="stat-icon"><i class="fa fa-users"></i></div>
                        <div class="stat-value">{{ $employees->count() }}</div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon"><i class="fa fa-user-check"></i></div>
                        <div class="stat-value">{{ $activeCount }}</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon"><i class="fa fa-user-clock"></i></div>
                        <div class="stat-value">{{ $nonActiveCount }}</div>
                        <div class="stat-label">Non-Active</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-icon"><i class="fa fa-user-times"></i></div>
                        <div class="stat-value">{{ $terminatedCount }}</div>
                        <div class="stat-label">Terminated</div>
                    </div>
                </div>

                <!-- Employees Card -->
                <div class="hr-card">
                    <div class="hr-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="search-box">
                                <i class="fa fa-search"></i>
                                <input type="search" id="empSearch" placeholder="Search employees...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm" id="refreshBtn"><i
                                        class="fa fa-sync"></i></button>
                            </div>
                        </div>
                        <span class="text-muted small" id="empCount">{{ $employees->count() }} employees</span>
                    </div>

                    <div class="hr-grid" id="empGrid">
                        @forelse($employees as $emp)
                            <div class="hr-item-card" data-id="{{ $emp->id }}"
                                data-name="{{ strtolower($emp->full_name) }}" data-email="{{ strtolower($emp->email) }}"
                                data-dept="{{ strtolower($emp->department->name ?? '') }}">
                                <div class="hr-item-header">
                                    <div class="d-flex align-items-center">
                                        <div class="hr-avatar">
                                            {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                        </div>
                                        <div class="hr-item-info">
                                            <h4 class="hr-item-name">{{ $emp->full_name }}</h4>
                                            <div class="hr-item-subtitle">{{ $emp->email }}</div>
                                            <div class="hr-item-meta">
                                                ID: {{ $emp->id }} • Joined
                                                {{ \Carbon\Carbon::parse($emp->joining_date)->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hr-actions">
                                        @can('hr.employees.edit')
                                            <button class="btn btn-edit edit-btn" title="Edit Employee">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        @endcan
                                        @can('hr.employees.delete')
                                            <button class="btn btn-delete delete-btn"
                                                data-url="{{ route('hr.employees.destroy', $emp->id) }}" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="hr-tags">
                                    <span class="hr-tag default"><i
                                            class="fa fa-building me-1"></i>{{ $emp->department->name ?? 'N/A' }}</span>
                                    <span class="hr-tag default"><i
                                            class="fa fa-briefcase me-1"></i>{{ $emp->designation->name ?? 'N/A' }}</span>
                                    <span
                                        class="hr-tag {{ $emp->status == 'active' ? 'success' : ($emp->status == 'non-active' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($emp->status) }}
                                    </span>
                                </div>

                                <!-- Hidden fields for edit -->
                                <input type="hidden" class="first_name" value="{{ $emp->first_name }}">
                                <input type="hidden" class="last_name" value="{{ $emp->last_name }}">
                                <input type="hidden" class="email" value="{{ $emp->email }}">
                                <input type="hidden" class="phone" value="{{ $emp->phone }}">
                                <input type="hidden" class="address" value="{{ $emp->address }}">
                                <input type="hidden" class="department_id" value="{{ $emp->department_id }}">
                                <input type="hidden" class="designation_id" value="{{ $emp->designation_id }}">
                                <input type="hidden" class="joining_date" value="{{ $emp->joining_date }}">
                                <input type="hidden" class="basic_salary" value="{{ $emp->basic_salary }}">
                                <input type="hidden" class="status" value="{{ $emp->status }}">
                                <input type="hidden" class="is_docs_submitted" value="{{ $emp->is_docs_submitted }}">
                                <input type="hidden" class="doc_degree" value="{{ $emp->getDocument('degree') }}">
                                <input type="hidden" class="doc_certificate"
                                    value="{{ $emp->getDocument('certificate') }}">
                                <input type="hidden" class="doc_hsc_marksheet"
                                    value="{{ $emp->getDocument('hsc_marksheet') }}">
                                <input type="hidden" class="doc_ssc_marksheet"
                                    value="{{ $emp->getDocument('ssc_marksheet') }}">
                                <input type="hidden" class="doc_cv" value="{{ $emp->getDocument('cv') }}">
                            </div>
                        @empty
                            <div class="empty-state" style="grid-column: 1/-1;">
                                <i class="fa fa-users"></i>
                                <p>No employees found. Add your first employee!</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-4 py-3 border-top">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header gradient">
                    <h5 class="modal-title" id="modalLabel">
                        <i class="fa fa-user-plus"></i>
                        <span>Add Employee</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="employeeForm" action="{{ route('hr.employees.store') }}" method="POST"
                    enctype="multipart/form-data" data-ajax-validate="true">
                    @csrf
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Personal Info -->
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-user"></i> First Name</label>
                                    <input type="text" name="first_name" id="first_name" class="form-control"
                                        placeholder="Enter first name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-user"></i> Last Name</label>
                                    <input type="text" name="last_name" id="last_name" class="form-control"
                                        placeholder="Enter last name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-envelope"></i> Email</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="Enter email address" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-lock"></i> Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control"
                                            placeholder="Leave blank to keep existing">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                            data-target="password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-phone"></i> Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control"
                                        placeholder="Enter phone number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-building"></i> Department</label>
                                    <select name="department_id" id="department_id" class="form-select" required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-briefcase"></i> Designation</label>
                                    <select name="designation_id" id="designation_id" class="form-select" required>
                                        <option value="">Select Designation</option>
                                        @foreach ($designations as $des)
                                            <option value="{{ $des->id }}">{{ $des->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-calendar"></i> Joining Date</label>
                                    <input type="date" name="joining_date" id="joining_date" class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-money-bill"></i> Basic Salary</label>
                                    <input type="number" step="0.01" name="basic_salary" id="basic_salary"
                                        class="form-control" placeholder="Enter basic salary" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="active">Active</option>
                                        <option value="non-active">Non-Active</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group-modern">
                                    <label class="form-label"><i class="fa fa-map-marker-alt"></i> Address</label>
                                    <textarea name="address" id="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_docs_submitted"
                                        id="is_docs_submitted" value="1">
                                    <label class="form-check-label" for="is_docs_submitted">Documents Submitted</label>
                                </div>
                            </div>

                            <!-- Documents -->
                            <div id="documents_container" class="row" style="display: none;">
                                <div class="col-12 mb-3">
                                    <h6 class="text-primary"><i class="fa fa-file-alt me-2"></i>Upload Documents</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label">Degree <span id="link_degree"></span></label>
                                        <input type="file" name="document_degree" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label">Certificate <span id="link_certificate"></span></label>
                                        <input type="file" name="document_certificate" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label">Intermediate Marksheet <span
                                                id="link_hsc_marksheet"></span></label>
                                        <input type="file" name="document_hsc_marksheet" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label">Matric Marksheet <span
                                                id="link_ssc_marksheet"></span></label>
                                        <input type="file" name="document_ssc_marksheet" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label">CV <span id="link_cv"></span></label>
                                        <input type="file" name="document_cv" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-modern">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-save">
                            <i class="fa fa-check"></i>
                            <span>Save Employee</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Create Employee
            $('#createBtn').click(function() {
                $('#edit_id').val('');
                $('#employeeForm')[0].reset();
                $('#documents_container').hide();
                $('#link_degree, #link_certificate, #link_hsc_marksheet, #link_ssc_marksheet, #link_cv')
                    .html('');
                $('#modalLabel').html('<i class="fa fa-user-plus"></i><span>Add Employee</span>');
                $('#employeeModal').modal('show');
            });

            // Edit Employee
            $(document).on('click', '.edit-btn', function() {
                var card = $(this).closest('.hr-item-card');
                $('#edit_id').val(card.data('id'));
                $('#first_name').val(card.find('.first_name').val());
                $('#last_name').val(card.find('.last_name').val());
                $('#email').val(card.find('.email').val());
                $('#phone').val(card.find('.phone').val());
                $('#address').val(card.find('.address').val());
                $('#department_id').val(card.find('.department_id').val());
                $('#designation_id').val(card.find('.designation_id').val());
                $('#joining_date').val(card.find('.joining_date').val());
                $('#basic_salary').val(card.find('.basic_salary').val());
                $('#status').val(card.find('.status').val());

                if (card.find('.is_docs_submitted').val() == '1') {
                    $('#is_docs_submitted').prop('checked', true);
                    $('#documents_container').show();
                } else {
                    $('#is_docs_submitted').prop('checked', false);
                    $('#documents_container').hide();
                }

                function setLink(id, filepath) {
                    if (filepath && filepath !== '') {
                        $('#' + id).html('<a href="{{ asset('') }}' + filepath +
                            '" target="_blank" class="text-primary small ms-2">(View)</a>');
                    } else {
                        $('#' + id).html('');
                    }
                }

                setLink('link_degree', card.find('.doc_degree').val());
                setLink('link_certificate', card.find('.doc_certificate').val());
                setLink('link_hsc_marksheet', card.find('.doc_hsc_marksheet').val());
                setLink('link_ssc_marksheet', card.find('.doc_ssc_marksheet').val());
                setLink('link_cv', card.find('.doc_cv').val());

                $('#modalLabel').html('<i class="fa fa-pen"></i><span>Edit Employee</span>');
                $('#employeeModal').modal('show');
            });

            // Toggle documents
            $('#is_docs_submitted').change(function() {
                $(this).is(':checked') ? $('#documents_container').slideDown() : $('#documents_container')
                    .slideUp();
            });

            // Delete Employee
            $(document).on('click', '.delete-btn', function() {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Delete Employee?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.success, 'success')
                                        .then(() => location.reload());
                                }
                            }
                        });
                    }
                });
            });

            // Search
            $('#empSearch').on('input', function() {
                var q = $(this).val().toLowerCase();
                $('.hr-item-card').each(function() {
                    var name = $(this).data('name') || '';
                    var email = $(this).data('email') || '';
                    var dept = $(this).data('dept') || '';
                    $(this).toggle(name.indexOf(q) !== -1 || email.indexOf(q) !== -1 || dept
                        .indexOf(q) !== -1);
                });
                $('#empCount').text($('.hr-item-card:visible').length + ' employees');
            });

            // Refresh
            $('#refreshBtn').click(() => location.reload());

            // Password Toggle Show/Hide
            $(document).on('click', '.toggle-password', function() {
                var targetId = $(this).data('target');
                var input = $('#' + targetId);
                var icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Custom submit handler removed - using data-ajax-validate
        });
    </script>
@endsection
