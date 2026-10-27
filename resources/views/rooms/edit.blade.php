
@extends('layouts.app')

@section('title', 'Edit Room - Pito Hotel')
@section('heading', 'Edit Hotel Room')

@section('content')

{{-- =====================================
     EDIT ROOM CARD
===================================== --}}

<div class="card p-4 mb-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-3 mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="d-flex align-items-center justify-content-center"
                 style="
                    width:55px;
                    height:55px;
                    background:#e0f3e7;
                    border-radius:14px;
                 ">

                <i class="bi bi-pencil-square text-success"
                   style="font-size:26px;"></i>

            </div>

            <div>
                <h4 class="text-success fw-bold mb-1">
                    Edit Room #{{ $room->room_number }}
                </h4>

                <p class="text-muted mb-0">
                    Update room information, price, status and picture.
                </p>
            </div>

        </div>

        {{-- BACK BUTTON --}}
        <a href="{{ route('rooms.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Back

        </a>

    </div>

    {{-- =====================================
         UPDATE ROOM FORM
    ===================================== --}}

    <form
        method="POST"
        action="{{ route('rooms.update', $room) }}"
        enctype="multipart/form-data"
    >

        @csrf
        @method('PUT')

        {{-- SHARED ROOM INPUT FIELDS --}}
        @include('rooms._form')

        {{-- ACTION BUTTONS --}}
        <div class="d-flex gap-2 flex-wrap mt-4">

            <button type="submit"
                    class="btn btn-success px-4">

                <i class="bi bi-check-circle me-1"></i>
                Update Room

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
