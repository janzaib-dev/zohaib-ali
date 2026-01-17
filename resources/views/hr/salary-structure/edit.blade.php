@extends('admin_panel.layout.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Salary Structure: {{ $employee->full_name }}</h3>
                            <a href="{{ route('hr.salary-structure.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>

                        @if ($readOnly ?? false)
                            <div class="alert alert-warning mb-3">
                                <i class="fa fa-eye"></i> <strong>View Only Mode:</strong> You have view permission only.
                                All fields are disabled.
                            </div>
                        @endif

                        <div class="border mt-1 shadow rounded p-4" style="background-color: white;">
                            <form id="salaryForm" action="{{ route('hr.salary-structure.update', $employee->id) }}"
                                method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Basic Info -->
                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-info">
                                            <strong>Employee:</strong> {{ $employee->full_name }} |
                                            <strong>Department:</strong> {{ $employee->department->name ?? 'N/A' }} |
                                            <strong>Designation:</strong> {{ $employee->designation->name ?? 'N/A' }} |
                                            <strong>Joining Date:</strong> {{ $employee->joining_date }}
                                        </div>
                                    </div>

                                    <!-- Salary Type -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Salary Type</label>
                                        <select name="salary_type" id="salary_type" class="form-control" required
                                            {{ $readOnly ?? false ? 'disabled' : '' }}>
                                            <option value="salary"
                                                {{ ($salaryStructure->salary_type ?? '') == 'salary' ? 'selected' : '' }}>
                                                Salary Based (Fixed)</option>
                                            <option value="commission"
                                                {{ ($salaryStructure->salary_type ?? '') == 'commission' ? 'selected' : '' }}>
                                                Commission Based</option>
                                            <option value="both"
                                                {{ ($salaryStructure->salary_type ?? '') == 'both' ? 'selected' : '' }}>
                                                Both
                                                (Salary + Commission)</option>
                                        </select>
                                    </div>

                                    <!-- Base Salary -->
                                    <div class="col-md-4 mb-3" id="base_salary_container">
                                        <label class="form-label fw-bold">Base Salary</label>
                                        <input type="number" step="0.01" name="base_salary" id="base_salary"
                                            class="form-control"
                                            value="{{ $salaryStructure->base_salary ?? ($employee->basic_salary ?? 0) }}"
                                            {{ $readOnly ?? false ? 'disabled' : '' }}>
                                    </div>

                                    <!-- Leave Salary Per Day -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Leave Salary Per Day</label>
                                        <input type="number" step="0.01" name="leave_salary_per_day"
                                            class="form-control" value="{{ $salaryStructure->leave_salary_per_day ?? '' }}"
                                            placeholder="For leave deductions" {{ $readOnly ?? false ? 'disabled' : '' }}>
                                    </div>

                                    <!-- Commission Settings -->
                                    <div class="col-md-12" id="commission_section" style="display: none;">
                                        <hr>
                                        <div class="card border-primary mb-3">
                                            <div class="card-header bg-primary text-white">
                                                <i class="fa fa-chart-line"></i> Commission Settings
                                            </div>
                                            <div class="card-body">
                                                <!-- Monthly Sales Target -->
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">
                                                            <i class="fa fa-bullseye text-danger"></i> Total Monthly Sales
                                                            Target
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rs.</span>
                                                            <input type="number" step="0.01" name="sales_target"
                                                                id="sales_target" class="form-control form-control-lg"
                                                                value="{{ $salaryStructure->sales_target ?? '' }}"
                                                                placeholder="e.g., 50000">
                                                        </div>
                                                        <small class="text-muted">Monthly sales target for the
                                                            employee</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">
                                                            <i class="fa fa-toggle-on text-success"></i> Commission Type
                                                        </label>
                                                        <div class="btn-group w-100" role="group">
                                                            <input type="radio" class="btn-check" name="commission_mode"
                                                                id="mode_flat" value="flat"
                                                                {{ !$salaryStructure->commission_tiers || count($salaryStructure->commission_tiers ?? []) == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-info" for="mode_flat">
                                                                <i class="fa fa-percent"></i> Flat Commission
                                                            </label>

                                                            <input type="radio" class="btn-check" name="commission_mode"
                                                                id="mode_tiered" value="tiered"
                                                                {{ $salaryStructure->commission_tiers && count($salaryStructure->commission_tiers ?? []) > 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-warning" for="mode_tiered">
                                                                <i class="fa fa-layer-group"></i> Tiered Commission
                                                            </label>
                                                        </div>
                                                        <small class="text-muted">Choose one: Flat % or Tiered rates</small>
                                                    </div>
                                                </div>

                                                <!-- Flat Commission Section -->
                                                <div id="flat_commission_section" class="card border-info mb-3"
                                                    style="display: none;">
                                                    <div class="card-header bg-info text-white">
                                                        <i class="fa fa-percent"></i> Flat Commission Rate
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Commission
                                                                    Percentage</label>
                                                                <div class="input-group input-group-lg">
                                                                    <input type="number" step="0.01" min="0"
                                                                        max="100" name="commission_percentage"
                                                                        id="commission_percentage" class="form-control"
                                                                        value="{{ $salaryStructure->commission_percentage ?? '' }}"
                                                                        placeholder="e.g., 5">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                                <small class="text-muted">This % applies to all
                                                                    sales</small>
                                                            </div>
                                                            <div class="col-md-6 d-flex align-items-center">
                                                                <div class="alert alert-info mb-0 w-100">
                                                                    <i class="fa fa-info-circle"></i>
                                                                    <strong>Example:</strong> 5% on Rs. 50,000 sales = Rs.
                                                                    2,500 commission
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tiered Commission Section -->
                                                <div id="tiered_commission_section" class="card border-warning"
                                                    style="display: none;">
                                                    <div
                                                        class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                                        <span><i class="fa fa-layer-group"></i> <strong>Commission
                                                                Tiers</strong></span>
                                                        <button type="button" class="btn btn-dark btn-sm"
                                                            id="addCommissionTier">
                                                            <i class="fa fa-plus"></i> Add Tier
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="alert alert-info py-2 mb-3">
                                                            <i class="fa fa-info-circle"></i>
                                                            <strong>How it works:</strong> Define commission % for each
                                                            sales range.
                                                            <br>
                                                            <small>Example: 2% for 0-10000, 5% for 10001-30000, 8% for
                                                                30001-50000</small>
                                                        </div>

                                                        <!-- Tier Headers -->
                                                        <div class="row mb-2 fw-bold text-muted" id="tier_headers"
                                                            style="display: none;">
                                                            <div class="col-md-1 text-center">#</div>
                                                            <div class="col-md-3">Commission %</div>
                                                            <div class="col-md-4">Sales Range</div>
                                                            <div class="col-md-3">Tier Covers</div>
                                                            <div class="col-md-1"></div>
                                                        </div>

                                                        <div id="commission_tiers_container">
                                                            @if ($salaryStructure->commission_tiers)
                                                                @php $prevAmount = 0; @endphp
                                                                @foreach ($salaryStructure->commission_tiers as $index => $tier)
                                                                    <div class="row mb-2 commission-tier-row align-items-center"
                                                                        data-index="{{ $index }}">
                                                                        <div class="col-md-1 text-center">
                                                                            <span
                                                                                class="badge bg-secondary tier-number">{{ $index + 1 }}</span>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <div class="input-group">
                                                                                <input type="number" step="0.01"
                                                                                    min="0" max="100"
                                                                                    name="commission_tiers[{{ $index }}][percentage]"
                                                                                    class="form-control tier-percentage"
                                                                                    placeholder="e.g., 5"
                                                                                    value="{{ $tier['percentage'] ?? '' }}">
                                                                                <span class="input-group-text">%</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="input-group">
                                                                                <span class="input-group-text">Up to
                                                                                    Rs.</span>
                                                                                <input type="number" step="0.01"
                                                                                    min="1"
                                                                                    name="commission_tiers[{{ $index }}][upto_amount]"
                                                                                    class="form-control tier-upto"
                                                                                    placeholder="e.g., 10000"
                                                                                    value="{{ $tier['upto_amount'] ?? '' }}">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <span
                                                                                class="tier-range-display badge bg-light text-dark">
                                                                                Rs. {{ number_format($prevAmount) }} -
                                                                                {{ number_format($tier['upto_amount'] ?? 0) }}
                                                                            </span>
                                                                        </div>
                                                                        <div class="col-md-1">
                                                                            <button type="button"
                                                                                class="btn btn-outline-danger btn-sm remove-row">
                                                                                <i class="fa fa-times"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    @php $prevAmount = $tier['upto_amount'] ?? 0; @endphp
                                                                @endforeach
                                                            @endif
                                                        </div>

                                                        <div id="no_tiers_message" class="text-center text-muted py-3"
                                                            style="{{ $salaryStructure->commission_tiers && count($salaryStructure->commission_tiers) > 0 ? 'display:none;' : '' }}">
                                                            <i class="fa fa-info-circle"></i> No commission tiers defined.
                                                            <br>Click "Add Tier" or use flat commission % above.
                                                        </div>

                                                        <div id="tier_validation_error"
                                                            class="alert alert-danger py-2 mt-2" style="display: none;">
                                                            <i class="fa fa-exclamation-triangle"></i> <span
                                                                id="tier_error_text"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Allowances Section -->
                                    <div class="col-md-12 mt-3">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <i class="fa fa-gift"></i> <strong>Allowances</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-light py-2 mb-3">
                                                    <i class="fa fa-info-circle text-success"></i>
                                                    Add monthly allowances like Housing, Transport, Medical, Food, etc.
                                                </div>

                                                <!-- Allowances Header -->
                                                <div class="row mb-2 fw-bold text-muted" id="allowance_headers"
                                                    style="{{ $salaryStructure->allowances && count($salaryStructure->allowances) > 0 ? '' : 'display:none;' }}">
                                                    <div class="col-md-1 text-center">#</div>
                                                    <div class="col-md-5">Allowance Name</div>
                                                    <div class="col-md-4">Amount (Rs.)</div>
                                                    <div class="col-md-2"></div>
                                                </div>

                                                <div id="allowances_container">
                                                    @if ($salaryStructure->allowances)
                                                        @foreach ($salaryStructure->allowances as $index => $allowance)
                                                            <div class="row mb-2 allowance-row align-items-center">
                                                                <div class="col-md-1 text-center">
                                                                    <span
                                                                        class="badge bg-success allowance-number">{{ $index + 1 }}</span>
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <input type="text"
                                                                        name="allowances[{{ $index }}][name]"
                                                                        class="form-control"
                                                                        placeholder="e.g., Housing Allowance"
                                                                        value="{{ $allowance['name'] ?? '' }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="input-group">
                                                                        <span class="input-group-text">Rs.</span>
                                                                        <input type="number" step="0.01"
                                                                            name="allowances[{{ $index }}][amount]"
                                                                            class="form-control" placeholder="Amount"
                                                                            value="{{ $allowance['amount'] ?? '' }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="button"
                                                                        class="btn btn-outline-danger btn-sm remove-row">
                                                                        <i class="fa fa-times"></i> Remove
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>

                                                <div id="no_allowances_message" class="text-center text-muted py-3"
                                                    style="{{ $salaryStructure->allowances && count($salaryStructure->allowances) > 0 ? 'display:none;' : '' }}">
                                                    <i class="fa fa-info-circle"></i> No allowances added yet.
                                                </div>

                                                <!-- Add Allowance Button at Bottom -->
                                                <div class="text-center mt-3 pt-3 border-top">
                                                    <button type="button" class="btn btn-success" id="addAllowance">
                                                        <i class="fa fa-plus-circle"></i> Add Allowance
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    @if (!($readOnly ?? false))
                                        <div class="col-md-12 mt-4">
                                            <hr>
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fa fa-save"></i> Save Salary Structure
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            var allowanceIndex = {{ count($salaryStructure->allowances ?? []) }};
            var deductionIndex = {{ count($salaryStructure->deductions ?? []) }};
            var commissionTierIndex = {{ count($salaryStructure->commission_tiers ?? []) }};
            var isReadOnly = {{ $readOnly ?? false ? 'true' : 'false' }};

            // If read-only mode, disable all inputs and hide action buttons
            if (isReadOnly) {
                $('#salaryForm input, #salaryForm select, #salaryForm textarea').prop('disabled', true);
                $('#salaryForm .remove-row').hide();
                $('#addAllowance, #addCommissionTier').hide();
                $('input[name="commission_mode"]').prop('disabled', true);
            }

            // Toggle commission section
            function toggleCommissionSection() {
                var type = $('#salary_type').val();
                if (type === 'commission' || type === 'both') {
                    $('#commission_section').slideDown();
                } else {
                    $('#commission_section').slideUp();
                }

                if (type === 'commission') {
                    $('#base_salary_container').hide();
                } else {
                    $('#base_salary_container').show();
                }
            }

            // Toggle between Flat and Tiered commission mode
            function toggleCommissionMode() {
                var mode = $('input[name="commission_mode"]:checked').val();
                if (mode === 'flat') {
                    $('#flat_commission_section').slideDown();
                    $('#tiered_commission_section').slideUp();
                    // Clear tiers when switching to flat
                    $('#commission_percentage').prop('disabled', false);
                } else {
                    $('#flat_commission_section').slideUp();
                    $('#tiered_commission_section').slideDown();
                    // Clear flat commission when switching to tiered
                    $('#commission_percentage').val('').prop('disabled', true);
                }
            }

            $('input[name="commission_mode"]').change(toggleCommissionMode);
            $('#salary_type').change(toggleCommissionSection);

            // Initial calls
            toggleCommissionSection();
            toggleCommissionMode();

            // Update tier display
            function updateTierDisplay() {
                var tiers = $('.commission-tier-row');
                var tierCount = tiers.length;

                // Show/hide headers and no-tiers message
                if (tierCount > 0) {
                    $('#tier_headers').show();
                    $('#no_tiers_message').hide();
                } else {
                    $('#tier_headers').hide();
                    $('#no_tiers_message').show();
                }

                // Update tier numbers and range display
                var prevAmount = 0;
                tiers.each(function(index) {
                    $(this).find('.tier-number').text(index + 1);
                    var uptoAmount = parseFloat($(this).find('.tier-upto').val()) || 0;
                    $(this).find('.tier-range-display').text('Rs. ' + prevAmount.toLocaleString() + ' - ' +
                        uptoAmount.toLocaleString());
                    prevAmount = uptoAmount;
                });
            }

            // Validate tiers
            function validateTiers() {
                var salesTarget = parseFloat($('#sales_target').val()) || 0;
                var tiers = $('.commission-tier-row');
                var isValid = true;
                var errorMsg = '';
                var prevAmount = 0;

                tiers.each(function(index) {
                    var uptoAmount = parseFloat($(this).find('.tier-upto').val()) || 0;

                    // Check if tier exceeds sales target
                    if (salesTarget > 0 && uptoAmount > salesTarget) {
                        isValid = false;
                        errorMsg = 'Tier ' + (index + 1) + ' amount (Rs. ' + uptoAmount.toLocaleString() +
                            ') exceeds sales target (Rs. ' + salesTarget.toLocaleString() + ')';
                        $(this).find('.tier-upto').addClass('is-invalid');
                    } else {
                        $(this).find('.tier-upto').removeClass('is-invalid');
                    }

                    // Check if tiers are in ascending order
                    if (uptoAmount <= prevAmount && uptoAmount > 0) {
                        isValid = false;
                        errorMsg = 'Tier ' + (index + 1) + ' must be greater than previous tier (Rs. ' +
                            prevAmount.toLocaleString() + ')';
                        $(this).find('.tier-upto').addClass('is-invalid');
                    }

                    prevAmount = uptoAmount;
                });

                if (!isValid) {
                    $('#tier_validation_error').show();
                    $('#tier_error_text').text(errorMsg);
                } else {
                    $('#tier_validation_error').hide();
                }

                return isValid;
            }

            // Event listeners for validation
            $(document).on('input', '.tier-upto, #sales_target', function() {
                updateTierDisplay();
                validateTiers();
            });

            // Initial display update
            updateTierDisplay();

            // Add Commission Tier Row
            $('#addCommissionTier').click(function() {
                var tierNum = $('.commission-tier-row').length + 1;
                var html = `
                    <div class="row mb-2 commission-tier-row align-items-center" data-index="${commissionTierIndex}">
                        <div class="col-md-1 text-center">
                            <span class="badge bg-secondary tier-number">${tierNum}</span>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="commission_tiers[${commissionTierIndex}][percentage]" class="form-control tier-percentage" placeholder="e.g., 5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Up to Rs.</span>
                                <input type="number" step="0.01" min="1" name="commission_tiers[${commissionTierIndex}][upto_amount]" class="form-control tier-upto" placeholder="e.g., 10000">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <span class="tier-range-display badge bg-light text-dark">Rs. 0 - ?</span>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                `;
                $('#commission_tiers_container').append(html);
                commissionTierIndex++;
                updateTierDisplay();
            });

            // Remove row handler update
            $(document).on('click', '.remove-row', function() {
                $(this).closest('.row').remove();
                updateTierDisplay();
                validateTiers();
            });

            // Update allowance display
            function updateAllowanceDisplay() {
                var allowances = $('.allowance-row');
                var count = allowances.length;

                if (count > 0) {
                    $('#allowance_headers').show();
                    $('#no_allowances_message').hide();
                } else {
                    $('#allowance_headers').hide();
                    $('#no_allowances_message').show();
                }

                // Update numbers
                allowances.each(function(index) {
                    $(this).find('.allowance-number').text(index + 1);
                });
            }

            // Initial call
            updateAllowanceDisplay();

            // Add Allowance Row
            $('#addAllowance').click(function() {
                var num = $('.allowance-row').length + 1;
                var html = `
                    <div class="row mb-2 allowance-row align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="badge bg-success allowance-number">${num}</span>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="allowances[${allowanceIndex}][name]" class="form-control" placeholder="e.g., Housing Allowance">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" name="allowances[${allowanceIndex}][amount]" class="form-control" placeholder="Amount">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fa fa-times"></i> Remove</button>
                        </div>
                    </div>
                `;
                $('#allowances_container').append(html);
                allowanceIndex++;
                updateAllowanceDisplay();
            });

            // Update remove-row handler to also update allowance display
            $(document).off('click', '.remove-row').on('click', '.remove-row', function() {
                $(this).closest('.row').remove();
                updateTierDisplay();
                validateTiers();
                updateAllowanceDisplay();
            });

            // Form Submit with validation
            $('#salaryForm').submit(function(e) {
                e.preventDefault();

                // Validate tiers before submitting
                if (!validateTiers()) {
                    Swal.fire('Validation Error', 'Please fix commission tier errors before saving.',
                        'error');
                    return;
                }

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.success, 'success').then(() => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            });
                        } else if (response.errors) {
                            Swal.fire('Error', response.errors.join('<br>'), 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            });
        });
    </script>
@endsection
