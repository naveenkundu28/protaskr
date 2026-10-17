@extends('layouts.app')

@section('title', 'Create Story')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/project-module.css') }}"
    >
@endpush

@section('content')

    <div class="container mt-80 pm-page pm-form-wrap">

        <div class="pm-card pm-form">

            {{-- Form Header --}}
            <div class="pm-form-title">

                <div>
                    <p class="pm-eyebrow">
                        {{ $project->name }}
                    </p>

                    <h2>
                        Create New Story
                    </h2>
                </div>

                <a
                    class="btn-close"
                    href="{{ route('projects.show', [$project, 'tab' => 'stories']) }}"
                ></a>

            </div>

            {{-- Create Story Form --}}
            <form
                method="POST"
                action="{{ route('stories.store') }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="project_id"
                    value="{{ $project->id }}"
                >

                <div class="row g-4">

                    {{-- Row 1: Project & Story Title --}}
                    <div class="col-md-6">
                        <label>
                            Project
                        </label>
                        <input
                            class="form-control"
                            value="{{ $project->name }}"
                            disabled
                        >
                    </div>

                    <div class="col-md-6">
                        <label>
                            Story Title <b>*</b>
                        </label>
                        <input
                            class="form-control"
                            name="title"
                            value="{{ old('title') }}"
                            required
                        >
                    </div>

                    {{-- Row 2: Priority & Due Date --}}
                    <div class="col-md-6">
                        <label>
                            Priority
                        </label>
                        <select
                            class="form-select"
                            name="priority"
                        >
                            <option value="medium">
                                Medium
                            </option>
                            <option value="high">
                                High
                            </option>
                            <option value="low">
                                Low
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>
                            Due Date
                        </label>
                        <input
                            class="form-control"
                            type="date"
                            name="due_date"
                            value="{{ old('due_date') }}"
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="story_points">Story Point</label>
                        <input
                            class="form-control"
                            type="number"
                            id="story_points"
                            name="story_points"
                            min="0"
                            step="1"
                            value="{{ old('story_points') }}"
                        >
                    </div>

                    {{-- Row 3: Description --}}
                    <div class="col-12">
                        <label>
                            Description
                        </label>
                        <textarea
                            class="form-control summernote"
                            rows="5"
                            id="story_description"
                            name="description"
                        >{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label for="acceptance_criteria">Acceptance Criteria</label>
                        <textarea
                            class="form-control summernote"
                            rows="5"
                            id="acceptance_criteria"
                            name="acceptance_criteria"
                        >{{ old('acceptance_criteria') }}</textarea>
                    </div>

                </div>

                {{-- Form Actions --}}
                <div class="pm-actions">

                    <a
                        class="btn btn-cancel"
                        href="{{ route('projects.show', [$project, 'tab' => 'stories']) }}"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary pm-button"
                    >
                        Create Story
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection
