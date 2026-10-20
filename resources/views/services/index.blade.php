@extends('layouts.app')
@extends('layouts.header')
@section('title')
    Services
@endsection

@section('content')
<div class="container-fluid mt-80">

    {{-- Header Banner --}}
    <div class="d-flex justify-content-between align-items-center bg-white mb-4 shadow-sm p-3 rounded">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <h2 class="mb-0">Services</h2>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-dark" href="{{ route('services.listing') }}">
                Service Listing
            </a>
            <a class="btn btn-primary" href="{{ route('clients.index') }}">
                Back
            </a>
        </div>
    </div>




    {{-- Modal: Copy Services Between Months --}}
    <div class="modal fade" id="copyServicesModal" tabindex="-1" aria-labelledby="copyServicesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="copyServicesModalForm" method="POST" action="{{ route('services.copyPrevious') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="copyServicesModalLabel">
                            <i class="bi bi-copy me-2 text-primary"></i>Copy Services Between Months
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Select the source month to copy services from, and the target month to copy them into.
                        </p>

                        <div class="mb-3">
                            <label for="modal_from_month" class="form-label fw-semibold">
                                Copy From (Source Month) <span class="text-danger">*</span>
                            </label>
                            <input type="month" name="from_month" id="modal_from_month" class="form-control" required>
                            <div class="form-text">The month containing the services you want to copy.</div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_to_month" class="form-label fw-semibold">
                                Copy To (Target Month) <span class="text-danger">*</span>
                            </label>
                            <input type="month" name="to_month" id="modal_to_month" class="form-control" required>
                            <div class="form-text">The month where duplicated services will be saved.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="confirmCopyBtn">
                            <i class="bi bi-check2 me-1"></i> Copy Services
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Form with Table inside Card Body --}}
    <form id="bulkServicesForm" method="POST" action="{{ route('services.save') }}">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <label for="select_month" class="fw-semibold text-secondary small mb-0">Month:</label>
                        <input type="month" name="selected_month" id="select_month" class="form-control form-control-sm"
                            style="width: 170px;" value="{{ $selectedMonthStr ?? now()->format('Y-m') }}">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="copyPreviousMonthBtn"
                            data-bs-toggle="modal" data-bs-target="#copyServicesModal">
                            <i class="bi bi-copy me-1"></i> Copy from previous month
                        </button>
                        @if ($services->isNotEmpty())
                            <span class="badge bg-light text-primary border small ms-1">
                                <i class="bi bi-check2-circle me-1"></i> {{ $services->count() }} service(s) found
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border small ms-1">
                                No records &bull; 5 empty rows ready
                            </span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="tableAddRowBtn">
                            <i class="bi bi-plus-lg me-1"></i> Add Row
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm d-none delete-selected-rows-btn" id="topDeleteSelectedRowsBtn">
                            <i class="bi bi-trash3 me-1"></i> Delete Row (<span class="selected-rows-count">0</span>)
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0" id="servicesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center py-2" style="width: 48px;">
                                    <input type="checkbox" class="d-none" id="selectAllRows">
                                    <span class="header-index-badge" id="selectAllBadge" title="Click to Select / Deselect All">#</span>
                                </th>
                                <th style="min-width: 165px;">Client <span class="text-danger">*</span></th>
                                <th style="min-width: 100px;">Type <span class="text-danger">*</span></th>
                                <th style="min-width: 110px;">Service ID <span class="text-danger">*</span></th>
                                <th style="min-width: 85px;">Hours <span class="text-danger">*</span></th>
                                <th style="min-width: 115px;">Status <span class="text-danger">*</span></th>
                                <th style="min-width: 155px;">Front Resource <span class="text-danger">*</span></th>
                                <th style="min-width: 155px;">Working Resource <span class="text-danger">*</span></th>
                                <th style="min-width: 120px;">Start Date</th>
                                <th style="min-width: 120px;">End Date</th>
                                <th style="min-width: 140px;">Comment</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTableBody">

                            @if ($services->isNotEmpty())
                                {{-- Display existing services from the database --}}
                                @foreach ($services as $index => $service)
                                    <tr class="service-row">
                                        <td class="text-center row-select-cell" title="Click to select row">
                                            <input type="checkbox" class="d-none row-select-checkbox">
                                            <span class="row-index-badge">{{ $index + 1 }}</span>
                                            <input type="hidden" name="services[{{ $index }}][id]" class="service-id-hidden" value="{{ $service->id }}">
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][client_id]" class="form-select client-select" required>
                                                <option value="">Select Client</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}" {{ $service->client_id == $client->id ? 'selected' : '' }}>
                                                        {{ $client->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][service_type]" class="form-select service-type-select" required>
                                                <option value="direct" {{ $service->service_type == 'direct' ? 'selected' : '' }}>Direct</option>
                                                <option value="proxy" {{ $service->service_type == 'proxy' ? 'selected' : '' }}>Proxy</option>
                                                <option value="external" {{ $service->service_type == 'external' ? 'selected' : '' }}>External</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="services[{{ $index }}][service_id]" class="form-control"
                                                placeholder="Service ID" value="{{ $service->service_id }}" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="services[{{ $index }}][hours]" class="form-control"
                                                placeholder="Hours" value="{{ $service->hours ? number_format($service->hours, 2, '.', '') : '' }}" required>
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][service_status]" class="form-select status-select" required>
                                                <option value="new" {{ $service->service_status == 'new' ? 'selected' : '' }}>New</option>
                                                <option value="in_progress" {{ $service->service_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="completed" {{ $service->service_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="replacement" {{ $service->service_status == 'replacement' ? 'selected' : '' }}>Replacement</option>
                                                <option value="canceled" {{ $service->service_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][front_resource]" class="form-select front-resource-select" required>
                                                <option value="">Select Resource</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}" {{ $service->front_resource == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->name }}{{ $employee->employee_id ? ' (' . $employee->employee_id . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            @php($workingResourceIds = $service->working_resource_ids)
                                            <select name="services[{{ $index }}][working_resource][]" class="form-select working-resource-select"
                                                data-saved-working-resources='@json(array_values($workingResourceIds))'
                                                {{ $service->service_type === 'proxy' ? 'multiple' : '' }} required>
                                                <option value="">Select Resource</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}" {{ in_array($employee->id, $workingResourceIds) ? 'selected' : '' }}>
                                                        {{ $employee->name }}{{ $employee->employee_id ? ' (' . $employee->employee_id . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="date" name="services[{{ $index }}][start_date]" class="form-control"
                                                value="{{ $service->start_date ? \Carbon\Carbon::parse($service->start_date)->format('Y-m-d') : '' }}">
                                        </td>
                                        <td>
                                            <input type="date" name="services[{{ $index }}][end_date]" class="form-control"
                                                value="{{ $service->end_date ? \Carbon\Carbon::parse($service->end_date)->format('Y-m-d') : '' }}"
                                                {{ in_array($service->service_status, ['completed', 'canceled']) ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="services[{{ $index }}][comment]" class="form-control"
                                                placeholder="Comment"
                                                value="{{ $service->cancel_reason ?? '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Display 5 empty rows if no services found for the selected month --}}
                                @for ($index = 0; $index < 5; $index++)
                                    <tr class="service-row">
                                        <td class="text-center row-select-cell" title="Click to select row">
                                            <input type="checkbox" class="d-none row-select-checkbox">
                                            <span class="row-index-badge">{{ $index + 1 }}</span>
                                            <input type="hidden" name="services[{{ $index }}][id]" class="service-id-hidden" value="">
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][client_id]" class="form-select client-select" required>
                                                <option value="" selected>Select Client</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}">
                                                        {{ $client->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][service_type]" class="form-select service-type-select" required>
                                                <option value="direct" selected>Direct</option>
                                                <option value="proxy">Proxy</option>
                                                <option value="external">External</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="services[{{ $index }}][service_id]" class="form-control"
                                                placeholder="Service ID" value="" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="services[{{ $index }}][hours]" class="form-control"
                                                placeholder="Hours" value="" required>
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][service_status]" class="form-select status-select" required>
                                                <option value="new" selected>New</option>
                                                <option value="in_progress">In Progress</option>
                                                <option value="completed">Completed</option>
                                                <option value="replacement">Replacement</option>
                                                <option value="canceled">Canceled</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][front_resource]" class="form-select front-resource-select" required>
                                                <option value="" selected>Select Resource</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}">
                                                        {{ $employee->name }}{{ $employee->employee_id ? ' (' . $employee->employee_id . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="services[{{ $index }}][working_resource][]" class="form-select working-resource-select" required>
                                                <option value="" selected>Select Resource</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}">
                                                        {{ $employee->name }}{{ $employee->employee_id ? ' (' . $employee->employee_id . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="date" name="services[{{ $index }}][start_date]" class="form-control" value="">
                                        </td>
                                        <td>
                                            <input type="date" name="services[{{ $index }}][end_date]" class="form-control" value="" disabled>
                                        </td>
                                        <td>
                                            <input type="text" name="services[{{ $index }}][comment]" class="form-control"
                                                placeholder="Comment" value="">
                                        </td>
                                    </tr>
                                @endfor
                            @endif

                        </tbody>
                    </table>
                </div>

                {{-- Card Bottom Action Bar --}}
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="bottomAddRowBtn">
                            <i class="bi bi-plus-lg me-1"></i> Add Row
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm d-none delete-selected-rows-btn" id="deleteSelectedRowsBtn">
                            <i class="bi bi-trash3 me-1"></i> Delete Row (<span class="selected-rows-count">0</span>)
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Save Services
                    </button>
                </div>

            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let rowIndex = $('#servicesTableBody tr').length;

    function updateRowNumbers() {
        $('#servicesTableBody tr').each(function(index) {
            $(this).find('.row-index-badge').text(index + 1);
            $(this).find('input, select').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    let updatedName = name.replace(/services\[\d+\]/, 'services[' + index + ']');
                    $(this).attr('name', updatedName);
                }
            });
        });
    }

    function createRow(index) {
        let firstRow = $('#servicesTableBody tr:first');
        let newRow = firstRow.clone();

        // Do not carry Select2's generated UI into a cloned row.
        newRow.find('.select2-container').remove();
        newRow.find('.working-resource-select')
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id aria-hidden tabindex data-saved-working-resources');
        newRow.removeClass('row-selected');
        newRow.find('.row-select-checkbox').prop('checked', false);
        newRow.find('input.service-id-hidden').val('');
        newRow.find('input.service-id-hidden').not(':first').remove();
        newRow.find('input.service-id-hidden').first().attr('name', 'services[' + index + '][id]');
        newRow.find('input[type="text"]').val('');
        newRow.find('input[type="number"]').val('');
        newRow.find('input[type="date"]').val('');
        newRow.find('select.client-select').val('');
        newRow.find('select.service-type-select').val('direct');
        newRow.find('select.status-select').val('new');
        newRow.find('select.front-resource-select').val('');
        newRow.find('select.working-resource-select').val('');
        newRow.find('input[name*="[comment]"], input[name*="[reason]"]')
            .val('')
            .attr('placeholder', 'Comment')
            .prop('disabled', false)
            .removeClass('bg-light')
            .css('pointer-events', 'auto');

        handleStatusDependencyForRow(newRow, true);

        return newRow;
    }

    // Reload page with selected month passed in the query parameter
    $('#select_month').on('change', function() {
        let monthVal = $(this).val();
        if (monthVal) {
            window.location.href = "{{ route('services.add') }}?month=" + encodeURIComponent(monthVal);
        }
    });

    // Initialize modal input values when the modal opens
    $('#copyServicesModal').on('show.bs.modal', function() {
        let currentSelectedMonth = $('#select_month').val();
        if (currentSelectedMonth) {
            let parts = currentSelectedMonth.split('-');
            let year = parseInt(parts[0], 10);
            let month = parseInt(parts[1], 10);

            // Target defaults to currently selected month
            $('#modal_to_month').val(currentSelectedMonth);

            // Source defaults to the previous month
            let prevDate = new Date(year, month - 2, 1);
            let prevYear = prevDate.getFullYear();
            let prevMonth = String(prevDate.getMonth() + 1).padStart(2, '0');
            $('#modal_from_month').val(`${prevYear}-${prevMonth}`);
        }
    });

    // Handle modal form submit with confirmation
    $('#copyServicesModalForm').on('submit', function(e) {
        let fromVal = $('#modal_from_month').val();
        let toVal = $('#modal_to_month').val();

        if (!fromVal || !toVal) {
            e.preventDefault();
            toast('Please select both source and target months.', 'warning');
            return false;
        }

        if (fromVal === toVal) {
            e.preventDefault();
            toast('Source and target months cannot be the same. Please choose different months.', 'warning');
            return false;
        }

        let fromParts = fromVal.split('-');
        let toParts = toVal.split('-');
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        let fromName = monthNames[parseInt(fromParts[1], 10) - 1] + ' ' + fromParts[0];
        let toName = monthNames[parseInt(toParts[1], 10) - 1] + ' ' + toParts[0];

        if (!confirm(`Copy all services from ${fromName} into ${toName}?`)) {
            e.preventDefault();
            return false;
        }

        $('#confirmCopyBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Copying...');
    });

    // Handle dependency between Service Type, Front Resource, and Working Resource
    function handleResourceForRow(tr) {
        let serviceType = tr.find('.service-type-select').val();
        let frontResource = tr.find('.front-resource-select').val();
        let workingSelect = tr.find('.working-resource-select');
        let isProxy = serviceType === 'proxy';
        let selectedWorkingResources = workingSelect.val();
        let savedResources = workingSelect.attr('data-saved-working-resources');

        if (savedResources) {
            try {
                selectedWorkingResources = JSON.parse(savedResources).map(String);
            } catch (error) {
                // Keep the current native select value if the saved attribute is malformed.
            }
            // Use the database values only for the first page-load initialization.
            workingSelect.removeAttr('data-saved-working-resources');
        }

        if (workingSelect.hasClass('select2-hidden-accessible')) {
            workingSelect.select2('destroy');
        }
        // Select2 normally removes this container on destroy; remove any leftover
        // generated UI so Direct/External always show only the native select.
        workingSelect.next('.select2').remove();

        if (!isProxy) {
            workingSelect.prop('multiple', false);
            workingSelect.val(frontResource);
            workingSelect
                .addClass('bg-light')
                .css('pointer-events', 'none')
                .attr('tabindex', '-1');
        } else {
            workingSelect.prop('multiple', true);
            workingSelect.val(selectedWorkingResources);
            workingSelect
                .removeClass('bg-light')
                .css('pointer-events', 'auto')
                .removeAttr('tabindex');

            if ($.fn.select2) {
                workingSelect.select2({
                    placeholder: 'Select Resource',
                    width: '100%',
                    allowClear: true,
                    // The select sits inside a responsive table, whose overflow can
                    // otherwise clip the Select2 options list.
                    dropdownParent: $(document.body)
                });
                workingSelect.val(selectedWorkingResources).trigger('change');
            }
        }
    }

    // Handle dependency of Service Status on End Date
    function handleStatusDependencyForRow(tr, isUserChange = false) {
        let status = tr.find('.status-select').val();
        let endDateInput = tr.find('input[name*="[end_date]"]');

        let isEnabled = (status === 'completed' || status === 'canceled');

        if (isEnabled) {
            endDateInput
                .prop('disabled', false)
                .removeClass('bg-light')
                .css('pointer-events', 'auto');
        } else {
            if (isUserChange) {
                endDateInput.val('');
            }
            endDateInput
                .prop('disabled', true)
                .addClass('bg-light')
                .css('pointer-events', 'none');
        }
    }

    function addRow() {
        let newRow = createRow(rowIndex);
        $('#servicesTableBody').append(newRow);
        handleResourceForRow(newRow);
        handleStatusDependencyForRow(newRow, false);
        rowIndex++;
        updateRowNumbers();
        updateSelectionState();
    }

    $('#tableAddRowBtn, #bottomAddRowBtn').on('click', function() {
        addRow();
    });

    // Update selection state and toggle Delete Selected buttons
    function updateSelectionState() {
        let total = $('#servicesTableBody tr').length;
        let selected = $('#servicesTableBody tr .row-select-checkbox:checked').length;

        $('.selected-rows-count').text(selected);

        if (selected > 0) {
            $('.delete-selected-rows-btn').removeClass('d-none');
        } else {
            $('.delete-selected-rows-btn').addClass('d-none');
        }

        let isAll = (total > 0 && selected === total);
        $('#selectAllRows').prop('checked', isAll);
        if (isAll) {
            $('#selectAllBadge').addClass('all-selected');
        } else {
            $('#selectAllBadge').removeClass('all-selected');
        }
    }

    // Toggle row selection on clicking the cell or number badge
    $(document).on('click', '.row-select-cell', function() {
        let tr = $(this).closest('tr');
        let cb = tr.find('.row-select-checkbox');
        let isChecked = !cb.prop('checked');
        cb.prop('checked', isChecked);
        if (isChecked) {
            tr.addClass('row-selected');
        } else {
            tr.removeClass('row-selected');
        }
        updateSelectionState();
    });

    // Master Select / Deselect All by clicking the header # badge
    $('#selectAllBadge').on('click', function() {
        let total = $('#servicesTableBody tr').length;
        let selected = $('#servicesTableBody tr .row-select-checkbox:checked').length;
        let shouldSelectAll = (selected < total);

        $('#servicesTableBody tr').each(function() {
            let tr = $(this);
            tr.find('.row-select-checkbox').prop('checked', shouldSelectAll);
            if (shouldSelectAll) {
                tr.addClass('row-selected');
            } else {
                tr.removeClass('row-selected');
            }
        });
        updateSelectionState();
    });

    // Delete selected rows
    $(document).on('click', '.delete-selected-rows-btn', function() {
        let selectedRows = $('#servicesTableBody tr').has('.row-select-checkbox:checked');
        let count = selectedRows.length;
        if (count === 0) return;

        if (confirm(`Are you sure you want to delete ${count} selected row(s)?`)) {
            selectedRows.each(function() {
                let tr = $(this);
                let existingId = tr.find('input.service-id-hidden').val();
                if (existingId) {
                    $('#bulkServicesForm').append('<input type="hidden" name="deleted_service_ids[]" value="' + existingId + '">');
                }
                tr.remove();
            });

            // Keep at least one row in the table
            if ($('#servicesTableBody tr').length === 0) {
                addRow();
            }

            updateRowNumbers();
            updateSelectionState();
        }
    });

    // When front resource changes: sync working resource if not proxy
    $(document).on('change', '.front-resource-select', function() {
        let tr = $(this).closest('tr');
        let serviceType = tr.find('.service-type-select').val();
        if (serviceType !== 'proxy') {
            tr.find('.working-resource-select').val($(this).val());
            tr.find('.working-resource-select').trigger('change.select2');
        }
    });

    // When service type changes: enable/disable working resource and sync
    $(document).on('change', '.service-type-select', function() {
        let tr = $(this).closest('tr');
        handleResourceForRow(tr);
    });

    // When service status changes: enable/disable End Date and Reason
    $(document).on('change', '.status-select', function() {
        let tr = $(this).closest('tr');
        handleStatusDependencyForRow(tr, true);
    });

    // Form submission validation: allow saving partially filled rows without browser required-errors on empty rows
    $('#bulkServicesForm').on('submit', function(e) {
        let rowsWithData = 0;
        $('#servicesTableBody tr').each(function() {
            let tr = $(this);
            let clientId = tr.find('.client-select').val();
            let serviceId = tr.find('input[name*="[service_id]"]').val();
            let hours = tr.find('input[name*="[hours]"]').val();

            if (!clientId && (!serviceId || !serviceId.trim()) && (!hours || !hours.trim())) {
                tr.find('input, select').removeAttr('required');
            } else {
                rowsWithData++;
                tr.find('.client-select, .service-type-select, .status-select, .front-resource-select').attr('required', true);
                tr.find('input[name*="[service_id]"], input[name*="[hours]"]').attr('required', true);
                if (tr.find('.service-type-select').val() === 'proxy') {
                    tr.find('.working-resource-select').attr('required', true);
                }
            }
        });

        if (rowsWithData === 0) {
            e.preventDefault();
            toast('Please fill at least one service before saving.', 'warning');
            return false;
        }

        // Enable disabled inputs before submission so their empty values submit properly
        $(this).find('input:disabled').prop('disabled', false);
    });

    // Initialize all existing rows on load
    $('#servicesTableBody tr').each(function() {
        handleResourceForRow($(this));
        handleStatusDependencyForRow($(this), false);
    });

    // Run once more after external scripts have finished loading. This ensures
    // existing proxy options are enhanced even on a cold CDN load of Select2.
    $(window).on('load', function() {
        $('#servicesTableBody tr').each(function() {
            if ($(this).find('.service-type-select').val() === 'proxy') {
                handleResourceForRow($(this));
            }
        });
    });

    updateRowNumbers();
    updateSelectionState();
});
</script>
@endpush
