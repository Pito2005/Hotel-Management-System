
@extends('layouts.app')

@section('title', 'Edit Staff - Pito Hotel')
@section('heading', 'Edit Staff')

@section('content')

<div class="card p-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-3 mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="d-flex align-items-center justify-content-center"
                 style="width:55px;height:55px;
                        background:#e0f3e7;border-radius:14px;">

                <i class="bi bi-pencil-square text-success"
                   style="font-size:26px;"></i>
            </div>

            <div>
                <h4 class="text-success fw-bold mb-1">
                    Edit {{ $staff->name }}
                </h4>

                <p class="text-muted mb-0">
                    Update employee details and profile picture.
                </p>
            </div>

        </div>

        <a href="{{ route('staff.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

    </div>


    {{-- UPDATE FORM --}}
    <form method="POST"
          action="{{ route('staff.update', $staff) }}"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        {{-- SHARED FORM --}}
        @include('staff._form')

        {{-- ACTION BUTTONS --}}
        <div class="mt-4 d-flex gap-2">

            <button type="submit"
                    class="btn btn-success px-4">

                <i class="bi bi-floppy me-1"></i>
                Update Staff
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
