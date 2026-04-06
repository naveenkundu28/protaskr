@extends('layouts.app')
@section('title', 'Daily Task Reports')

@section('content')
    <div class="container-fluid">
        <div class="bg-white shadow-sm p-3 rounded mb-4">
            <div class="row">
                <h2 class="col-md-12 mb-0">Daily Task Reports</h2>

                <div class="col-md-6 mt-3">
                    {{-- <form method="GET" action="{{ route('daily.task.report') }}"> --}}
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <input type="date" class="form-control" id="tasksDate" name="tasksDate" max="{{ now()->format('Y-m-d') }}" value="{{ now()->subDay()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 mt-2">
                            <button type="button" id="tasksDateButton" class="btn btn-primary w-100"
                                style="padding-top: 7px !important; padding-bottom: 8px !important;">Filter</button>
                        </div>
                    </div>
                    {{-- </form> --}}
                </div>
            </div>
        </div>

        @if (Auth::user()->hasRole('Admin'))
            <div class="row mb-4">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3" id="tagsTabs" role="tablist">

                                <!-- Static "All" Tab -->
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-all" data-bs-toggle="tab"
                                        data-bs-target="#pane-all" type="button" role="tab" aria-controls="pane-all"
                                        aria-selected="true" data-tag-id="" data-accordion="dailyTasksAccordion-all">
                                        All
                                    </button>
                                </li>

                                <!-- Dynamic Tabs -->
                                @foreach ($tags as $index => $tag)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-{{ $index }}" data-bs-toggle="tab"
                                            data-bs-target="#pane-{{ $index }}" type="button" role="tab"
                                            aria-controls="pane-{{ $index }}" aria-selected="false"
                                            data-tag-id="{{ $tag->id }}"
                                            data-accordion="dailyTasksAccordion-{{ $index }}" {{-- style="background-color: {{ $tag->color }}; color: #fff;" --}}>
                                            {{ $tag->name }}
                                        </button>
                                    </li>
                                @endforeach

                            </ul>

                            <div class="tab-content" id="tagsTabsContent">

                                <!-- Static "All" Content -->
                                <div class="tab-pane fade show active" id="pane-all" role="tabpanel"
                                    aria-labelledby="tab-all" tabindex="0">
                                    <!-- All data -->


                                    <div class="accordion dailyTasksAccordion" id="dailyTasksAccordion-all">

                                    </div>


                                </div>

                                <!-- Dynamic Content -->
                                @foreach ($tags as $index => $tag)
                                    <div class="tab-pane fade" id="pane-{{ $index }}" role="tabpanel"
                                        aria-labelledby="tab-{{ $index }}" tabindex="0">


                                        <div class="accordion dailyTasksAccordion" id="dailyTasksAccordion-{{ $index }}">

                                        </div>



                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>


        @endif


    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function loadTasks(accordionId = 'dailyTasksAccordion-all', tagId = '') {
                let date = $('#tasksDate').val();

                $.ajax({
                    url: "{{ route('daily.task.report.users') }}",
                    type: "GET",
                    data: {
                        tasksDate: date,
                        tag_id: tagId
                    },
                    success: function(res) {
                        if (res.status) {
                            console.log('res', res.data);

                            renderTasks(res.data, accordionId);
                        }
                    }
                });
            }

            // On load
            loadTasks('dailyTasksAccordion-all');

            // On filter change
            $('#tasksDateButton').on('click', function() {
                loadTasks('dailyTasksAccordion-all');
            });

            function renderTasks(users, accordionId) {

                let html = '';

                if (!users || users.length === 0) {
                    $('#' + accordionId).html(`
                        <div class="text-center text-muted py-3">
                            No users found
                        </div>
                    `);
                    return;
                }

                users.forEach((user, uIndex) => {

                    let tasksRows = '';

                    if (user.tasks.length > 0) {

                        user.tasks.forEach((task, index) => {
                            tasksRows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${task.title ?? '-'}</td>
                                    <td>${task.project?.name ?? '-'}</td>
                                    <td>${task.total_task_time}</td>
                                    <td>${task.status ? formatStatus(task.status) : '-'}</td>
                                </tr>
                            `;
                        });

                    } else {
                        tasksRows = `
                            <tr>
                                <td colspan="5" class="text-center text-muted">No Tasks found</td>
                            </tr>
                        `;
                    }
                    
                    let bgColor = user.performance?.background_color ?? '#8b1c1c';
                    console.log(user.name, user.performance);
                    let fontColor = user.performance?.font_color ?? '#ffffff';
                    let performanceTitle = user.performance?.title ?? 'Absent';

                    html += `
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading-${uIndex}">
                            <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-${accordionId}-${uIndex}"
                                title="${performanceTitle}"
                                style="background-color: ${bgColor}; color: ${fontColor};">
                                ${user.name} ${user.employee_id ? '- ' + user.employee_id : ''} , ${user.total_time}
                            </button>
                        </h2>

                        <div id="collapse-${accordionId}-${uIndex}" class="accordion-collapse collapse"
                            data-bs-parent="#${accordionId}">

                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Task Details</th>
                                                <th>Client Name</th>
                                                <th>Time consumed</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${tasksRows}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    `;
                });

                $('#' + accordionId).html(html);
            }

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {

                let tagId = $(this).data('tag-id'); // null for All
                let accordionId = $(this).data('accordion');

                // prevent reload if already loaded
                // if ($('#' + accordionId).html().trim() !== '') return;

                loadTasks(accordionId, tagId);

            });

            function formatStatus(status) {
                return status
                    .replaceAll('_', ' ')
                    .replace(/\b\w/g, char => char.toUpperCase());
            }

        })
    </script>
@endpush
