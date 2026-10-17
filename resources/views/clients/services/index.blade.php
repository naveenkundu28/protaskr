@extends('layouts.app')

@section('title')
    Services - {{ $client->name }}
@endsection

@section('content')
    <div class="container-fluid mt-80">

        <div class="d-flex justify-content-between align-items-center bg-white mb-4 shadow-sm p-3 rounded">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h2 class="mb-0">Services - {{ $client->name }}</h2>
                <form method="GET" action="{{ route('clients.services.index', $client->id) }}"
                    class="d-flex gap-2 auto-search-form" data-search-target="#tableSearchResults">
                    <input type="hidden" name="tab" value="{{ $activeTab ?? 'active' }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search services">
                    {{-- <button class="btn btn-outline-secondary">Search</button> --}}
                </form>
            </div>

            <div>
                <a class="btn btn-primary" href="{{ route('clients.index') }}">
                    Back
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif


        {{-- ================= FORM ================= --}}

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h4 id="form_title">Add Service</h4>

                @error('service_err')
                    <span class="text-danger">{{ $message }}</span>
                @enderror

                <form id="serviceForm" action="{{ route('clients.services.store', $client->id) }}" method="POST">

                    @csrf
                    <input type="hidden" name="editing_service_id" id="editing_service_id" value="{{ old('editing_service_id') }}">

                    <div id="method_field"></div>

                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Service Type <span class="text-danger">*</span></label>
                            <select name="service_type" id="service_type" class="form-select" required>
                                <option value="direct" {{ old('service_type', 'direct') == 'direct' ? 'selected' : '' }}>
                                    Direct</option>
                                <option value="proxy" {{ old('service_type') == 'proxy' ? 'selected' : '' }}>Proxy
                                </option>
                                <option value="external" {{ old('service_type') == 'external' ? 'selected' : '' }}>External
                                </option>
                            </select>

                            @error('service_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Service ID <span class="text-danger">*</span></label>
                            <input type="text" name="service_id" id="service_id" value="{{ old('service_id') }}"
                                class="form-control" required>
                            @error('service_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Hours <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="hours" id="hours" value="{{ old('hours') }}"
                                class="form-control" required>
                            @error('hours')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Month <span class="text-danger">*</span></label>
                            <select name="month" id="month" class="form-select" required>
                                <option value="">Select Month</option>
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}"
                                        {{ old('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>

                            @error('month')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Year <span class="text-danger">*</span></label>
                            <select name="year" id="year" class="form-select" required>
                                <option value="">Select Year</option>

                                @php
                                    $currentYear = now()->year;
                                @endphp

                                @for ($y = $currentYear - 15; $y <= $currentYear ; $y++)
                                    <option value="{{ $y }}"
                                        {{ old('year', now()->year) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>

                            @error('year')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="service_status" id="service_status" class="form-select" required>
                                <option value="new" {{ old('service_status') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="in_progress" {{ old('service_status') == 'in_progress' ? 'selected' : '' }}>
                                    In Progress</option>
                                <option value="completed" {{ old('service_status') == 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                                <option value="replacement" {{ old('service_status') == 'replacement' ? 'selected' : '' }}>
                                    Replacement</option>
                                <option value="canceled" {{ old('service_status') == 'canceled' ? 'selected' : '' }}>
                                    Canceled</option>
                            </select>
                            @error('service_status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Front Resource <span class="text-danger">*</span></label>
                            <select name="front_resource" id="front_resource" class="form-select" required>
                                <option value="">Select</option>

                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ old('front_resource') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}{{ $employee->employee_id ? ' - ' . $employee->employee_id : '' }}
                                    </option>
                                @endforeach

                            </select>
                            @error('front_resource')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Working Resource <span class="text-danger">*</span></label>
                            <select name="working_resource[]" id="working_resource" class="form-select" required>
                                <option value="">Select</option>

                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ in_array($employee->id, (array) old('working_resource', [])) ? 'selected' : '' }}>
                                        {{ $employee->name }}{{ $employee->employee_id ? ' - ' . $employee->employee_id : '' }}
                                    </option>
                                @endforeach

                            </select>
                            @error('working_resource')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @error('working_resource.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3 d-none" id="start_date_div">
                            <label>Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" id="start_date"
                                class="form-control">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3 d-none" id="end_date_div">
                            <label>End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" id="end_date"
                                class="form-control">
                            @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3 d-none" id="reason_div">
                            <label>Cancel Reason <span class="text-danger">*</span></label>
                            <textarea name="cancel_reason" id="reason" class="form-control">{{ old('cancel_reason') }}</textarea>
                            @error('cancel_reason')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 d-none" id="replacement_reason_div">
                            <label>Replacement Reason <span class="text-danger">*</span></label>
                            <textarea name="replacement_reason" id="replacement_reason" class="form-control">{{ old('replacement_reason') }}</textarea>
                            @error('replacement_reason')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                    </div>

                    <div class="text-center">
                        <button class="btn btn-primary" id="submitBtn">
                            Create Service
                        </button>
                    </div>

                    <button type="button" class="btn btn-secondary d-none" id="cancelEdit">
                        Cancel
                    </button>

                </form>

            </div>
        </div>



        {{-- ================= TABLE ================= --}}
        <ul class="nav nav-pills service-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ ($activeTab ?? 'active') === 'active' ? 'active' : '' }}"
                    href="{{ route('clients.services.index', ['client' => $client->id, 'tab' => 'active', 'search' => request('search')]) }}">
                    Active Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($activeTab ?? 'active') === 'closed' ? 'active' : '' }}"
                    href="{{ route('clients.services.index', ['client' => $client->id, 'tab' => 'closed', 'search' => request('search')]) }}">
                    Closed Services
                </a>
            </li>
        </ul>

        <div id="tableSearchResults">
            <div class="table-responsive service-table-responsive">
                <table class="table table-bordered">

                    <tr>
                        <th>S.No</th>
                        <th>Type</th>
                        <th>Front Resource</th>
                        <th>Working Resource</th>
                        <th>Service ID</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Hours</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        @if (($activeTab ?? 'active') === 'closed')
                            <th>End Date</th>
                            <th style="width:250px">Reason</th>
                        @endif
                        <th>Action</th>
                    </tr>


                    @foreach ($services as $service)
                        <tr>

                            <td>{{ $services->firstItem() + $loop->index }}</td>

                            <td>{{ ucfirst($service->service_type) }}</td>

                            <td>

                                @if ($service->frontResource)
                                    <span class="{{ $service->frontResource->trashed() ? 'text-danger' : '' }}">
                                        {{ $service->frontResource->name }}
                                    </span>
                                @endif

                            </td>
                            <td>

                                @include('services.partials.working-resources', ['service' => $service])

                            </td>

                            <td>{{ $service->service_id }}</td>

                            <td>{{ $service->month ? \Carbon\Carbon::create()->month($service->month)->format('F') : '' }} </td>
                            <td>{{ $service->year ?? '' }}</td>
                            @php
                                $year = $service->created_at->format('Y');
                                $month = $service->created_at->format('F');

                                $key = $year . '-' . $month;

                                $masterHour = $masterHours[$key]->hours ?? null;

                                $color = '';

                                if ($masterHour !== null) {
                                    if ($service->hours < $masterHour) {
                                        $color = 'text-warning'; // yellow
                                    } elseif ($service->hours > $masterHour) {
                                        $color = 'text-primary'; // dark blue
                                    }
                                }
                            @endphp

                            <td class="{{ $color }}">
                                {{ $service->hours }}
                            </td>

                            <td>{{ ucfirst(str_replace('_', ' ', $service->service_status)) }}</td>

                            <td>
                                {{ $service->start_date
                                    ? \Carbon\Carbon::parse($service->start_date)->format('j F Y')
                                    : ($service->main_start_date
                                        ? \Carbon\Carbon::parse($service->main_start_date)->format('j F Y')
                                        : '') }}
                            </td>

                            @if (($activeTab ?? 'active') === 'closed')
                                <td>{{ $service->end_date ? \Carbon\Carbon::parse($service->end_date)->format('j F Y') : '' }}
                                </td>

                                <td style="width:250px">
                                    {{ $service->cancel_reason ?? ($service->replacement_reason ?? '') }}
                                </td>
                            @endif

                            <td>
                                <div class="dropdown service-action-dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-list-task"></i>
                                    </button>
                                    <ul class="dropdown-menu py-0">

                                        @can('service-show')
                                            <li class="">
                                                <a href="{{ route('clients.services.show', [$client->id, $service->id]) }}"
                                                    class="px-2 py-2 px-1 text-primary text-decoration-none d-block">
                                                    Show
                                                </a>
                                            </li>
                                        @endcan

                                        @can('service-update')
                                            <li>
                                                <hr class="my-1" />
                                            </li>
                                            <li class="">
                                                <button class="border-0 text-info text-start w-100 bg-white editService px-2 py-2"
                                                    data-service='@json($service)'>
                                                    Edit
                                                </button>
                                            </li>
                                        @endcan


                                        @can('service-create')
                                            <li>
                                                <hr class="my-1" />
                                            </li>
                                            <li class="">
                                                <button class="px-2 py-2 border-0 text-success text-start w-100 bg-white copyService"
                                                    data-service='@json($service)'>
                                                    Copy
                                                </button>
                                            </li>
                                        @endcan



                                        @can('service-delete')
                                            <li>
                                                <hr class="my-1" />
                                            </li>

                                            <li class="">
                                                <form
                                                    action="{{ route('clients.services.destroy', [$client->id, $service->id]) }}"
                                                    method="POST" style="display:inline">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="px-2 py-2 border-0 text-danger text-start w-100 bg-white"
                                                        onclick="return confirm('Are you sure you want to delete this service?')">
                                                        Delete
                                                    </button>

                                                </form>
                                            </li>
                                        @endcan

                                    </ul>
                                </div>
                            </td>

                        </tr>
                    @endforeach

                </table>
            </div>

            {{ $services->links('pagination::bootstrap-5') }}
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .service-tabs {
            gap: 0.5rem;
        }

        .service-tabs .nav-link {
            border: 1px solid #dfe3ea;
            border-radius: 999px;
            color: #495057;
            background: #f8f9fa;
            font-weight: 600;
            padding: 0.45rem 1rem;
            transition: all 0.2s ease;
        }

        .service-tabs .nav-link:hover {
            color: #0d6efd;
            border-color: #9ec5fe;
            background: #eef5ff;
        }

        .service-tabs .nav-link.active {
            color: #fff;
            background: #0d6efd;
            border-color: #0d6efd;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.22);
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            // --------------to handle service type & resources--------------
            function handleResource() {
                let serviceType = $('#service_type').val();

                if (serviceType !== 'proxy') {
                    $('#working_resource').prop('multiple', false);
                    $('#working_resource').val($('#front_resource').val());
                    $('#working_resource')
                        .addClass('bg-light')
                        .css('pointer-events', 'none') // prevents change
                    // $('#working_resource').prop('disabled', true);
                } else {
                    // $('#working_resource').prop('disabled', false);
                    $('#working_resource').prop('multiple', true);
                    $('#working_resource')
                        .removeClass('bg-light')
                        .css('pointer-events', 'auto');
                }
            }

            // when service type changes
            $('#service_type').change(function() {
                handleResource();
            });

            // when front resource changes
            $('#front_resource').change(function() {
                if ($('#service_type').val() !== 'proxy') {
                    $('#working_resource').val($(this).val());
                }
            });

            handleResource(); // run on page load

            // --------------END: to handle service type & resources--------------



            function toggleFields() {

                let status = $('#service_status').val()

                $('#start_date_div').addClass('d-none')
                $('#end_date_div').addClass('d-none')
                $('#reason_div').addClass('d-none')
                $('#replacement_reason_div').addClass('d-none')

                if (status === 'new')
                    $('#start_date_div').removeClass('d-none')

                if (status === 'completed')
                    $('#end_date_div').removeClass('d-none')

                if (status === 'canceled') {
                    $('#reason_div').removeClass('d-none')
                    $('#end_date_div').removeClass('d-none')
                }
                if (status === 'replacement') {
                    $('#replacement_reason_div').removeClass('d-none')
                    $('#end_date_div').removeClass('d-none')
                }

            }

            toggleFields()

            $('#service_status').on('change', toggleFields)



            function fillForm(service, isCopy = false) {
                console.log('res', service);


                $('#service_type').val(service.service_type)
                $('#service_id').val(service.service_id)
                $('#hours').val(service.hours)
                $('#month').val(service.month)
                $('#year').val(service.year)
                $('#service_status').val(service.service_status)
                $('#front_resource').val(service.front_resource?.id)
                handleResource() // sets single/multiple mode for the selected service type
                $('#working_resource').val(String(service.working_resource || '').split(',').filter(Boolean))
                $('#start_date').val(service.start_date)
                $('#end_date').val(service.end_date)
                $('#reason').val(service.cancel_reason)
                $('#replacement_reason').val(service.replacement_reason)

                toggleFields()
                handleResource()
                let client = "{{ $client->id }}"

                if (!isCopy) {

                    let updateUrl = "{{ route('clients.services.update', [$client->id, ':serviceId']) }}";
                    updateUrl = updateUrl.replace(':serviceId', service?.id);

                    $('#serviceForm').attr('action', updateUrl);



                    $('#method_field').html(
                        '<input type="hidden" name="_method" value="PUT">'
                    )
                    $('#editing_service_id').val(service?.id)

                    $('#submitBtn').text('Update Service')
                    $('#form_title').text('Edit Service')

                } else {

                    $('#serviceForm').attr(
                        'action',
                        "{{ route('clients.services.store', $client->id) }}"
                    )

                    // $('#serviceForm').attr(
                    //     'method', "POST"
                    // )

                    $('#method_field').html('')
                    $('#editing_service_id').val('')

                    $('#submitBtn').text('Create Service')
                    $('#form_title').text('Copy Service')

                }

                $('#cancelEdit').removeClass('d-none')

                $('html,body').animate({
                    scrollTop: 0
                }, 400)

            }



            $('.editService').click(function() {

                let service = $(this).data('service')

                fillForm(service, false)

            })


            $('.copyService').click(function() {

                let service = $(this).data('service')

                fillForm(service, true)

            })


            $('#cancelEdit').click(function() {

                location.reload()

            })

            const oldEditingServiceId = @json(old('editing_service_id'));

            if (oldEditingServiceId) {
                const matchingEditButton = $('.editService').filter(function() {
                    const service = $(this).data('service');
                    return String(service?.id) === String(oldEditingServiceId);
                }).first();

                if (matchingEditButton.length) {
                    fillForm(matchingEditButton.data('service'), false);
                }
            }


        })
    </script>
@endpush
