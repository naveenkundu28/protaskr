@extends('layouts.app')

@section('title')
    Services - {{ $client->name }}
@endsection

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center bg-white mb-4 shadow-sm p-3 rounded">
            <h2>Services - {{ $client->name }}</h2>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- <pre>
            {{ print_r($services) }}
        </pre> --}}

        {{-- ================= FORM ================= --}}

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h4 id="form_title">Add Service</h4>

                <form id="serviceForm" action="{{ route('clients.services.store', $client->id) }}" method="POST">

                    @csrf

                    <div id="method_field"></div>

                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Service Type</label>
                            <select name="service_type" id="service_type" class="form-select" required>
                                <option value="direct">Direct</option>
                                <option value="proxy">Proxy</option>
                                <option value="external">External</option>
                            </select>

                            @error('service_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Service ID</label>
                            <input type="text" name="service_id" id="service_id" class="form-control" required>
                            @error('service_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Hours</label>
                            <input type="number" step="0.01" name="hours" id="hours" class="form-control"
                                required>
                            @error('hours')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Status</label>
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
                            <label>Front Resource</label>
                            <select name="front_resource" id="front_resource" class="form-select">
                                <option value="">Select</option>

                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }}{{ $employee->employee_id ? ' - ' . $employee->employee_id : '' }}
                                    </option>
                                @endforeach

                            </select>
                            @error('front_resource')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label>Working Resource</label>
                            <select name="working_resource" id="working_resource" class="form-select">
                                <option value="">Select</option>

                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }}{{ $employee->employee_id ? ' - ' . $employee->employee_id : '' }}
                                    </option>
                                @endforeach

                            </select>
                            @error('working_resource')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3 d-none" id="start_date_div">
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3 d-none" id="end_date_div">
                            <label>End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                            @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3 d-none" id="reason_div">
                            <label>Cancel Reason</label>
                            <textarea name="cancel_reason" id="reason" class="form-control"></textarea>
                            @error('cancel_reason')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 d-none" id="replacement_reason_div">
                            <label>Replacement Reason</label>
                            <textarea name="replacement_reason" id="replacement_reason" class="form-control"></textarea>
                            @error('replacement_reason')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                    </div>


                    <button class="btn btn-primary" id="submitBtn">
                        Create Service
                    </button>

                    <button type="button" class="btn btn-secondary d-none" id="cancelEdit">
                        Cancel
                    </button>

                </form>

            </div>
        </div>



        {{-- ================= TABLE ================= --}}

        <table class="table table-bordered">

            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Front Resource</th>
                <th>Working Resource</th>
                <th>Service ID</th>
                <th>Month</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th style="width:250px">Reason</th>
                <th width="220">Action</th>
            </tr>


            @foreach ($services as $service)
                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ ucfirst($service->service_type) }}</td>

                    <td>

                        @if ($service->frontResource)
                            <span>
                                {{ $service->frontResource->name }}
                            </span>
                        @endif

                    </td>
                    <td>

                        @if ($service->workingResource)
                            <span>
                                {{ $service->workingResource->name }}
                            </span>
                        @endif

                    </td>

                    <td>{{ $service->service_id }}</td>

                    <td>{{ $service->created_at ? $service->created_at->format('F') : '' }}</td>
                    <td>{{ $service->hours }}</td>

                    <td>{{ ucfirst(str_replace('_', ' ', $service->service_status)) }}</td>

                    <td>
                        {{ $service->start_date
                            ? \Carbon\Carbon::parse($service->start_date)->format('j F Y')
                            : ($service->main_start_date
                                ? \Carbon\Carbon::parse($service->main_start_date)->format('j F Y')
                                : '') }}
                    </td>

                    <td>{{ $service->end_date ? \Carbon\Carbon::parse($service->end_date)->format('j F Y') : '' }}</td>

                    <td style="width:250px">
                        {{ $service->cancel_reason ?? ($service->replacement_reason ?? '') }}
                    </td>

                    <td>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-list-task"></i>
                            </button>
                            <ul class="dropdown-menu py-0">

                                @can('service-show')
                                    <li class="px-2 py-2">
                                        <a href="{{ route('clients.services.show', [$client->id, $service->id]) }}"
                                            class="px-1 text-primary text-decoration-none d-block">
                                            Show
                                        </a>
                                    </li>
                                @endcan

                                @can('service-edit')
                                    <li>
                                        <hr class="my-1" />
                                    </li>
                                    <li class="px-2 py-2">
                                        <button class="border-0 text-info text-start w-100 bg-white editService"
                                            data-service='@json($service)'>
                                            Edit
                                        </button>
                                    </li>
                                @endcan


                                @can('service-create')
                                    <li>
                                        <hr class="my-1" />
                                    </li>
                                    <li class="px-2 py-2">
                                        <button class="border-0 text-success text-start w-100 bg-white copyService"
                                            data-service='@json($service)'>
                                            Copy
                                        </button>
                                    </li>
                                @endcan



                                @can('service-delete')
                                    <li>
                                        <hr class="my-1" />
                                    </li>

                                    <li class="px-2 py-2">
                                        <form action="{{ route('clients.services.destroy', [$client->id, $service->id]) }}"
                                            method="POST" style="display:inline">

                                            @csrf
                                            @method('DELETE')

                                            <button class="border-0 text-danger text-start w-100 bg-white"
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

        {{ $services->links('pagination::bootstrap-5') }}

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // --------------to handle service type & resources--------------
            function handleResource() {
                let serviceType = $('#service_type').val();

                if (serviceType !== 'proxy') {
                    $('#working_resource').val($('#front_resource').val());
                    $('#working_resource')
                        .addClass('bg-light')
                        .css('pointer-events', 'none') // prevents change
                    // $('#working_resource').prop('disabled', true);
                } else {
                    // $('#working_resource').prop('disabled', false);
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
                $('#service_status').val(service.service_status)
                $('#front_resource').val(service.front_resource?.id)
                $('#working_resource').val(service.working_resource?.id)
                $('#start_date').val(service.start_date)
                $('#end_date').val(service.end_date)
                $('#reason').val(service.cancel_reason)
                $('#replacement_reason').val(service.replacement_reason)

                toggleFields()

                let client = "{{ $client->id }}"

                if (!isCopy) {

                    let updateUrl = "{{ route('clients.services.update', [$client->id, ':serviceId']) }}";
                    updateUrl = updateUrl.replace(':serviceId', service?.id);

                    $('#serviceForm').attr('action', updateUrl);



                    $('#method_field').html(
                        '<input type="hidden" name="_method" value="PUT">'
                    )

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


        })
    </script>
@endpush
