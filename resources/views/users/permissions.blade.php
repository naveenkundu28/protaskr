@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-80">
        <div class="row">
            <div class="col-lg-12 mb-4 margin-tb">
                <div class="d-flex align-items-center justify-content-between shadow-sm p-3 rounded bg-white">
                    <div class="pull-left">
                        <h2>Permissions - {{ $user->name }}</h2>
                        <div class="text-muted">
                            Role: {{ $user->role?->name ?? 'None' }}
                            &middot; {{ count($granted) }} of {{ collect($groups)->flatten()->count() }} permissions
                        </div>
                    </div>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ route('users.index') }}"><i class="fa fa-arrow-left"></i>
                            Back</a>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('users.permissions.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="permission-masonry">
                @foreach ($groups as $group => $permissions)
                    <div class="permission-masonry-item">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h5 class="card-title mb-0">{{ ucfirst($group) }}</h5>
                                    <a href="#" class="small permission-group-toggle" data-group="{{ $group }}">All / none</a>
                                </div>
                                @foreach ($permissions as $permission)
                                    @php
                                        $isGranted = in_array($permission, $granted, true);
                                        $isDefault = in_array($permission, $defaults, true);
                                        $isLocked = $permission === $selfProtected;
                                    @endphp
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                            id="permission-{{ $permission }}" value="{{ $permission }}"
                                            data-group="{{ $group }}" {{ $isGranted || $isLocked ? 'checked' : '' }}
                                            {{ $isLocked ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="permission-{{ $permission }}">
                                            {{ $permission }}
                                            @if ($isLocked)
                                                <span class="text-muted small">(your own, always on)</span>
                                            @elseif ($isGranted && !$isDefault)
                                                <span class="badge bg-success">added</span>
                                            @elseif (!$isGranted && $isDefault)
                                                <span class="badge bg-warning text-dark">removed</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="permission-actions d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-sm p-3 rounded bg-white mb-4">
                <button type="submit" name="reset" value="1" class="btn btn-outline-secondary"
                    onclick="return confirm('Reset this user to the default permissions of their role?')">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to {{ $user->role?->name ?? 'role' }} defaults
                </button>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small"><span id="permission-count">{{ count($granted) }}</span> selected</span>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2 me-1"></i> Save Permissions
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            /* cards flow down each column, so short and tall groups pack without gaps */
            .permission-masonry {
                column-count: 1;
                column-gap: 1.5rem;
            }

            .permission-masonry-item {
                break-inside: avoid;
                margin-bottom: 1.5rem;
            }

            .permission-actions {
                position: sticky;
                bottom: 1rem;
                z-index: 5;
                margin-top: 0.5rem;
            }

            @media (min-width: 576px) {
                .permission-masonry {
                    column-count: 2;
                }
            }

            @media (min-width: 992px) {
                .permission-masonry {
                    column-count: 3;
                }
            }

            @media (min-width: 1400px) {
                .permission-masonry {
                    column-count: 4;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(document).on('click', '.permission-group-toggle', function(event) {
                event.preventDefault();
                const boxes = $('input[type=checkbox][data-group="' + $(this).data('group') + '"]:not(:disabled)');
                boxes.prop('checked', boxes.filter(':checked').length !== boxes.length);
                updatePermissionCount();
            });

            function updatePermissionCount() {
                $('#permission-count').text($('input[name="permissions[]"]:checked').length);
            }

            $(document).on('change', 'input[name="permissions[]"]', updatePermissionCount);
        </script>
    @endpush
@endsection
