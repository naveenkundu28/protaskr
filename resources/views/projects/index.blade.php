@extends('layouts.app')

@section('title', 'Projects')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/project-module.css') }}">
    <style>
        .projects-page-topbar {
            align-items: flex-start;
            gap: 16px;
        }
        .projects-page-topbar h1 {
            flex: 0 0 auto;
            margin: 0;
            white-space: nowrap;
        }
        .projects-page-topbar .right_search_box {
            min-width: 0;
            flex: 1 1 auto;
            justify-content: flex-end;
        }
        .projects-filter-form {
            position: static !important;
            left: auto !important;
            transform: none !important;
            display: flex;
            flex: 1 1 auto;
            min-width: 0;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .projects-filter-form .select2-container {
            flex: 1 1 190px;
            min-width: 0;
            max-width: 280px;
        }
        .projects-filter-form .select2-container .select2-selection--single {
            display: flex;
            align-items: center;
            height: 42px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .projects-filter-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal;
            padding: 0 36px 0 14px;
        }
        .projects-filter-form .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 10px;
        }
        .projects-filter-form input[name="search"] {
            flex: 1 1 200px;
            min-width: 160px;
            max-width: 320px;
        }
        @media (max-width: 1199.98px) {
            .projects-page-topbar {
                flex-wrap: wrap;
            }
            .projects-page-topbar h1 {
                flex-basis: 100%;
            }
            .projects-page-topbar .right_search_box,
            .projects-filter-form {
                width: 100%;
            }
            .projects-page-topbar .right_search_box {
                justify-content: flex-start;
            }
        }
        @media (max-width: 575.98px) {
            .projects-filter-form .select2-container,
            .projects-filter-form input[name="search"] {
                flex: 1 1 100%;
                max-width: none;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
    jQuery(function ($) {
        const $form = $('.projects-filter-form');
        if (!$form.length) return;
        let searchTimer;
        $form.on('change', 'select[name="client_id"], select[name="assigned_user_id"]', function () {
            $form.find('input[name="page"]').remove();
            $form[0].submit();
        });
        $form.on('input', 'input[name="search"]', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                $form.find('input[name="page"]').remove();
                $form[0].submit();
            }, 500);
        });
        $(document).on('click', '.pm-tabs a', async function (event) {
            event.preventDefault();
            const link = this;
            const tabs = document.querySelector('.pm-tabs');
            const card = document.querySelector('.pm-card');
            if (!tabs || !card) return;
            tabs.classList.add('is-loading');
            try {
                const response = await fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Unable to load projects');
                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                const nextTabs = nextDocument.querySelector('.pm-tabs');
                const nextCard = nextDocument.querySelector('.pm-card');
                if (nextTabs && nextCard) {
                    tabs.replaceWith(nextTabs);
                    card.replaceWith(nextCard);
                    history.pushState({}, '', link.href);
                }
            } catch (error) {
                window.location.assign(link.href);
            }
        });
    });
</script>
@endpush

@section('content')
<div class="container-fluid mt-80">
    <div class="pm-heading">
        <div class="inner_page_topbar projects-page-topbar">
            <h1>Projects</h1>
            <div class="right_search_box">
                <form method="GET" action="{{ route('projects.index') }}" class="search_box projects-filter-form">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    
                                        <select name="client_id" class="project-list-filter select2" data-placeholder="Select Client">
    <option value="">Select Client</option>
    @foreach($clients as $client)
        <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>
            {{ $client->name }}
        </option>
    @endforeach
</select>

<select name="assigned_user_id" class="project-list-filter select2" data-placeholder="Select Assigned User">
    <option value="">Select Assigned User</option>
    @foreach($users as $user)
        <option value="{{ $user->id }}" @selected(request('assigned_user_id') == $user->id)>
            {{ $user->name }}
        </option>
    @endforeach
</select>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Project...">
                    @if(request('search'))
                        <a href="{{ route('projects.index', array_filter(['status' => request('status')])) }}" class="search_clear_link" aria-label="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif

                    @if(
    request()->filled('client_id') ||
    request()->filled('assigned_user_id') ||
    request()->filled('search') ||
    (request()->filled('status') && request('status') !== 'all')
)
    <a href="{{ route('projects.index') }}"
       class="projects-clear-filters"
       aria-label="Clear filters"
       title="Clear filters">
        <i class="bi bi-x-lg"></i>
    </a>
@endif
                    
                </form>
                @can('projects-create')
                    <a class="btn btn-primary pm-button" href="#" onclick="openPopup()">
                        <i class="bi bi-plus-lg"></i> Create Project
                    </a>
                    <!-- <a class="btn btn-primary pm-button" href="{{ route('projects.create') }}" >
                        <i class="bi bi-plus-lg"></i> Create Project
                    </a> -->
                @endcan
            </div>
        </div>
        
    </div>

    <div class="pm-tabs">
        @foreach([
            'all' => 'All (' . $counts->total .')',
            'not_started' => 'Not Started (' . $counts->not_started .')',
            'in_progress' => 'In Progress (' . $counts->in_progress .')',
            'completed' => 'Completed (' . $counts->completed.')',
            'on_hold' => 'On Hold (' . $counts->on_hold.')'
        ] as $key => $label)
            <a 
                class="pm-tab-{{ $key }} {{ $status === $key ? 'active' : '' }}" 
                href="{{ route('projects.index', array_merge(request()->except(['status', 'page']), ['status' => $key])) }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="pm-card">
        <div class="table-responsive project_table">
            <table class="table pm-table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Project name</th>
                        <th>Client</th>
                        <th>Start date</th>
                        <th>End date</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        @php
                            $displayStatus = in_array($project->status, ['completed', 'on_hold'], true)
                                ? $project->status
                                : ((int) $project->project_tracked_seconds > 0 ? 'in_progress' : 'not_started');
                        @endphp
                        <tr>
                            <td>{{ $projects->firstItem() + $loop->index }}</td>
                            <td>
                                <a class="pm-link fw-semibold" href="{{ route('projects.show', $project) }}">
                                    {{ $project->name }}
                                </a>
                                <small class="d-none">{{ $project->project_type }}</small>
                            </td>
                            <td>{{ $project->client?->name ?: 'Internal' }}</td>
                            <td>{{ optional($project->start_date)->format('d M Y') ?: '—' }}</td>
                            <td>{{ optional($project->end_date)->format('d M Y') ?: '—' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar {{ $project->progress_color_class }}" style="width: {{ $project->progress }}%"></div>
                                    </div>
                                    <span>{{ $project->progress }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="pm-badge pm-status-{{ $displayStatus }} {{ $displayStatus }}">
                                    {{ str_replace('_', ' ', ucfirst($displayStatus)) }}
                                </span>
                                
                            </td>
                            <td>
                                <!-- <a class="btn btn-light btn-sm" href="{{ route('projects.show', $project) }}">
                                    <i class="bi bi-three-dots"></i>
                                </a> -->
                                <div class="project-actions">
                                    <button type="button" class="emp_status_action" aria-label="Project actions">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul>
                                        @can('projects-update')
                                            <li><a href="{{ route('projects.index', array_merge(request()->except(['edit', 'page']), ['edit' => $project->id])) }}">Edit</a></li>
                                        @endcan
                                        @can('projects-delete')
                                            <li>
                                                <form method="POST" class="mb-0" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="project-delete-action" type="submit">Delete</button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No projects match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($projects->hasPages())
                <div class="pagination">
                    <p>Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} Projects</p>
                    <ul>
                        <li class="prev_next_btn {{ $projects->onFirstPage() ? 'disabled' : '' }}">
                            @if ($projects->onFirstPage())
                                <span><i class="bi bi-chevron-left"></i></span>
                            @else
                                <a href="{{ $projects->previousPageUrl() }}" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                            @endif
                        </li>

                        @for($page = 1; $page <= $projects->lastPage(); $page++)
                            <li class="{{ $page === $projects->currentPage() ? 'active' : '' }}">
                                <a href="{{ $projects->url($page) }}">{{ $page }}</a>
                            </li>
                        @endfor

                        <li class="prev_next_btn {{ $projects->hasMorePages() ? '' : 'disabled' }}">
                            @if ($projects->hasMorePages())
                                <a href="{{ $projects->nextPageUrl() }}" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                            @else
                                <span><i class="bi bi-chevron-right"></i></span>
                            @endif
                        </li>
                    </ul>
                </div>
            @elseif ($projects->total())
                <div class="pagination">
                    <p>Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} Projects</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade pm-modal" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ $formProject->exists ? route('projects.update', $formProject) : route('projects.store') }}" class="modal-content">
            @csrf
            @if($formProject->exists) @method('PUT') @endif
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="projectModalLabel">{{ $formProject->exists ? 'Edit Project' : 'Create New Project' }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeProjectModal()"></button>
            </div>
            <div class="modal-body">
                    <div class="row g-3">
                        {{-- Row 1: Project Name, Project Type, Client (external only) --}}
                        <div class="col-md-6">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" value="{{ old('name', $formProject->name) }}" placeholder="Enter project name" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Project Type <span class="text-danger">*</span></label>
                            @php
                                $selectedProjectType = old(
                                    'project_type',
                                    $formProject->project_type ?: 'external'
                                );
                            @endphp
                            <select class="form-select" name="project_type" id="project_type" required>
                                <option value="external" @selected($selectedProjectType === 'external')>External</option>
                                <option value="internal" @selected($selectedProjectType === 'internal')>Internal</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="project-client-field">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select name="client_id" id="project_client_id" class="form-select select2">
                                <option value="">Select a client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id', $formProject->client_id) == $client->id)>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Row 2: Start Date, End Date, Status, Priority --}}
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date', optional($formProject->start_date)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ old('end_date', optional($formProject->end_date)->format('Y-m-d')) }}">
                        </div>
                        @if($formProject->exists)
                        <div class="col-md-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status">
                                @foreach(['completed' => 'Completed', 'on_hold' => 'On Hold'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('status', $formProject->status ?: 'not_started') === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" name="priority">
                                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('priority', $formProject->priority ?: 'medium') === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Row 3: Budget Hours, Team Members --}}
                        {{-- <div class="col-md-3">
                            <label class="form-label">Budget (Hours)</label>
                            <input type="number" min="0" step=".5" class="form-control" name="budget_hours" placeholder="0.0" value="{{ old('budget_hours', $formProject->budget_hours) }}">
                        </div> --}}
                        <div class="col-md-12">
                            <label class="form-label">Team Members</label>
                            <select class="form-select select2" name="member_ids[]" multiple size="4">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(in_array($user->id, old('member_ids', $formProject->exists ? $formProject->members->pluck('id')->all() : [])))>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Use Ctrl/Cmd to select multiple team members.</small>
                        </div>

                        {{-- Row 4: Description --}}
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control summernote" id="project_description" rows="4" name="description">{{ old('description', $formProject->description) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel px-4" data-bs-dismiss="modal" onclick="closeProjectModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary pm-button px-4">{{ $formProject->exists ? 'Save Changes' : 'Create Project' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openProjectModal() {
    var modalEl = document.getElementById("projectModal");
    if (modalEl) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function openPopup() {
    openProjectModal();
}

function closeProjectModal() {
    var modalEl = document.getElementById("projectModal");
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
    window.history.replaceState({}, document.title, "{{ route('projects.index', request()->except('edit')) }}");
}

function closePopup() {
    closeProjectModal();
}

document.addEventListener("DOMContentLoaded", function () {
    var modalEl = document.getElementById("projectModal");
    var projectType = document.getElementById("project_type");
    var clientField = document.getElementById("project-client-field");
    var clientSelect = document.getElementById("project_client_id");

    function toggleClientField() {
        var isExternal = projectType && projectType.value === 'external';
        clientField.classList.toggle('d-none', !isExternal);
        clientSelect.required = isExternal;
        clientSelect.disabled = !isExternal;
    }

    if (projectType && clientField && clientSelect) {
        projectType.addEventListener('change', toggleClientField);
        toggleClientField();
    }

    if (modalEl) {
        modalEl.addEventListener("hidden.bs.modal", function () {
            window.history.replaceState({}, document.title, "{{ route('projects.index', request()->except('edit')) }}");
        });
        modalEl.addEventListener("shown.bs.modal", function () {
            $(modalEl).find('.select2').select2({
                dropdownParent: $('#projectModal'),
                width: '100%'
            });
            if (typeof window.initSummernoteEditors === 'function') {
                window.initSummernoteEditors();
            }
        });
    }

    @if($formProject->exists || $errors->any())
        openProjectModal();
    @endif
});
</script>





@endsection
