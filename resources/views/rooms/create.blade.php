
@extends('layouts.app')

@section('title', 'Add Room - Pito Hotel')
@section('heading', 'Add New Hotel Room')

@section('content')

<div class="card p-4 mb-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h4 class="text-success fw-bold">
                <i class="bi bi-plus-circle me-2"></i>
                Add New Room
            </h4>

            <p class="text-muted mb-0">
                Enter room information and upload a picture.
            </p>
        </div>

        <a href="{{ route('rooms.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

    </div>

    {{-- CREATE ROOM FORM --}}
    <form
        method="POST"
        action="{{ route('rooms.store') }}"
        enctype="multipart/form-data"
    >

        @csrf

        {{-- SHARED FORM --}}
        @include('rooms._form')

        {{-- BUTTONS --}}
        <div class="mt-4 d-flex gap-2 flex-wrap">

            <button type="submit"
                    class="btn btn-success px-4">

                <i class="bi bi-floppy me-1"></i>
                Save Room

            </button>

            <a href="{{ route('rooms.index') }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-x-circle me-1"></i>
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection
