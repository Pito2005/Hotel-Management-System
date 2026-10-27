
@extends('layouts.app')

@section('title', 'New Booking - Pito Hotel')
@section('heading', 'Create New Booking')

@section('content')

<div class="card p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-success">
            <i class="bi bi-calendar-plus me-2"></i>
            New Room Reservation
        </h4>

        <a href="{{ route('bookings.index') }}"
           class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    {{-- BOOKING FORM --}}
    <form method="POST" action="{{ route('bookings.store') }}">
        @csrf

        <div class="row g-3">

            {{-- SELECT EXISTING GUEST --}}
            <div class="col-md-6">
                <label class="form-label">Hotel Guest *</label>

                <select name="guest_id" class="form-select" required>
                    <option value="">Select Guest</option>

                    @foreach($guests as $guest)
                        <option value="{{ $guest->id }}"
                                @selected(old('guest_id') == $guest->id)>
                            {{ $guest->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- SELECT HOTEL ROOM --}}
            <div class="col-md-6">
                <label class="form-label">Hotel Room *</label>

                <select name="room_id" class="form-select" required>
                    <option value="">Select Room</option>

                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}"
                                @selected(old('room_id') == $room->id)>
                            Room {{ $room->room_number }}
                            - {{ $room->roomType?->name }}
                            - ${{ number_format($room->price_per_night, 2) }}/night
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- CHECK IN --}}
            <div class="col-md-6">
                <label class="form-label">Check-In Date *</label>

                <input type="date"
                       name="check_in"
                       min="{{ now()->toDateString() }}"
                       value="{{ old('check_in') }}"
                       class="form-control"
                       required>
            </div>

            {{-- CHECK OUT --}}
            <div class="col-md-6">
                <label class="form-label">Check-Out Date *</label>

                <input type="date"
                       name="check_out"
                       value="{{ old('check_out') }}"
                       class="form-control"
                       required>
            </div>

            {{-- NUMBER OF ADULTS --}}
            <div class="col-md-6">
                <label class="form-label">Number of Adults *</label>

                <input type="number"
                       name="adults"
                       value="{{ old('adults', 1) }}"
                       min="1"
                       max="20"
                       class="form-control"
                       required>
            </div>

            {{-- NOTES --}}
            <div class="col-12">
                <label class="form-label">Booking Notes</label>

                <textarea name="notes"
                          class="form-control"
                          rows="3"
                          placeholder="Special guest requests...">{{ old('notes') }}</textarea>
            </div>

            <div class="col-12">
                <div class="alert alert-info">
                    The total price will be calculated automatically
                    based on the room price and number of nights.
                </div>
            </div>

            <div class="col-12">
                <button class="btn btn-success">
                    <i class="bi bi-check-circle me-1"></i>
                    Save Booking
                </button>

                <a href="{{ route('bookings.index') }}"
                   class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

@endsection
