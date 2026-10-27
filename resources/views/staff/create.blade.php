
@extends('layouts.app')

@section('title', 'Add Staff - Pito Hotel')
@section('heading', 'Add New Staff')

@section('content')

<div class="card p-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-3 mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="d-flex align-items-center justify-content-center"
                 style="width:55px;height:55px;
                        background:#e0f3e7;border-radius:14px;">

                <i class="bi bi-person-plus-fill text-success"
                   style="font-size:27px;"></i>
            </div>

            <div>
                <h4 class="text-success fw-bold mb-1">
                    Create Staff Account
                </h4>

                <p class="text-muted mb-0">
                    Add a new employee to Pito Hotel.
                </p>
            </div>

        </div>

        <a href="{{ route('staff.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

    </div>


    {{-- STAFF CREATE FORM --}}
    <form method="POST"
          action="{{ route('staff.store') }}"
          enctype="multipart/form-data">

        @csrf

        {{-- SHARED FORM --}}
        @include('staff._form')

        {{-- ACTION BUTTONS --}}
        <div class="mt-4 d-flex gap-2">

            <button type="submit"
                    class="btn btn-success px-4">

                <i class="bi bi-person-check me-1"></i>
                Save Staff
            </button>

            <a href="{{ route('staff.index') }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-x-circle me-1"></i>
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection
