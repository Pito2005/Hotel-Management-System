
/*
|--------------------------------------------------------------------------
| PITO HOTEL - SMART ROOM AVAILABILITY
|--------------------------------------------------------------------------
| Features:
| 1. Check available rooms by Check-In and Check-Out dates
| 2. Hide rooms already booked for selected dates
| 3. Display available room count
| 4. Update room price calculation
| 5. Restore selected room after validation errors
| 6. Handle loading, invalid dates and network errors
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ==========================================
    // ELEMENTS
    // ==========================================

    const checkIn = document.getElementById('bookingCheckIn');
    const checkOut = document.getElementById('bookingCheckOut');
    const roomSelect = document.getElementById('bookingRoom');
    const roomHelp = document.getElementById('bookingRoomHelp');

    const bookingDialog = document.getElementById(
        'newBookingDialog'
    );

    const oldState = document.getElementById('rvOldState');

    if (!checkIn || !checkOut || !roomSelect) {
        return;
    }

    // ==========================================
    // STATE
    // ==========================================

    let currentRequest = null;
    let requestNumber = 0;

    let previousRoomId = oldState?.dataset.roomId || '';

    // ==========================================
    // HELPER: DISPLAY MESSAGE
    // ==========================================

    function setMessage(message, isError = false) {
        if (!roomHelp) {
            return;
        }

        roomHelp.textContent = message;

        roomHelp.classList.toggle(
            'text-danger',
            isError
        );

        roomHelp.classList.toggle(
            'text-muted',
            !isError
        );
    }

    // ==========================================
    // HELPER: NOTIFY PRICE CALCULATOR
    // ==========================================

    function updatePrice() {
        roomSelect.dispatchEvent(
            new Event('change', {
                bubbles: true
            })
        );
    }

    // ==========================================
    // HELPER: CLEAR ROOM DROPDOWN
    // ==========================================

    function clearRooms(message) {
        roomSelect.replaceChildren(
            new Option(message, '')
        );

        roomSelect.value = '';
        roomSelect.disabled = true;

        updatePrice();
    }

    // ==========================================
    // CHECK ROOM AVAILABILITY
    // ==========================================

    async function loadAvailableRooms() {

        const checkInDate = checkIn.value;
        const checkOutDate = checkOut.value;

        // Prevent older responses from changing
        // the current room list.
        requestNumber++;

        const thisRequest = requestNumber;

        // Cancel previous request.
        if (currentRequest) {
            currentRequest.abort();
            currentRequest = null;
        }

        // ======================================
        // VALIDATE DATES
        // ======================================

        if (!checkInDate || !checkOutDate) {

            clearRooms(
                'Select Check-In and Check-Out first'
            );

            setMessage(
                'Choose your dates to see available rooms.'
            );

            return;
        }

        if (checkOutDate <= checkInDate) {

            clearRooms('Invalid date range');

            setMessage(
                'Check-Out must be later than Check-In.',
                true
            );

            return;
        }

        // ======================================
        // REMEMBER PREVIOUS ROOM
        // ======================================

        const selectedRoomId =
            roomSelect.value || previousRoomId;

        previousRoomId = '';

        // ======================================
        // LOADING STATE
        // ======================================

        clearRooms('Checking available rooms...');

        setMessage('Checking room availability...');

        const controller = new AbortController();

        currentRequest = controller;

        // ======================================
        // REQUEST TO LARAVEL
        // ======================================

        try {

            const endpoint =
                roomSelect.dataset.availabilityUrl;

            if (!endpoint) {
                throw new Error(
                    'Room availability URL is missing.'
                );
            }

            const url = new URL(
                endpoint,
                window.location.origin
            );

            url.searchParams.set(
                'check_in',
                checkInDate
            );

            url.searchParams.set(
                'check_out',
                checkOutDate
            );

            const response = await fetch(
                url.toString(),
                {
                    method: 'GET',

                    credentials: 'same-origin',

                    signal: controller.signal,

                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Failed to fetch available rooms.'
                );
            }

            const data = await response.json();

            // Ignore results from old requests.
            if (thisRequest !== requestNumber) {
                return;
            }

            const rooms = Array.isArray(data.rooms)
                ? data.rooms
                : [];

            // ==================================
            // NO AVAILABLE ROOMS
            // ==================================

            if (rooms.length === 0) {

                clearRooms('No rooms available');

                setMessage(
                    'No rooms available for these dates. ' +
                    'Please select another date range.',
                    true
                );

                return;
            }

            // ==================================
            // BUILD ROOM OPTIONS
            // ==================================

            roomSelect.replaceChildren(
                new Option(
                    'Select Available Room',
                    ''
                )
            );

            rooms.forEach(function (room) {

                const price = Number(
                    room.price_per_night
                );

                const priceText =
                    Number.isFinite(price)
                        ? price.toFixed(2)
                        : '0.00';

                const roomLabel =
                    'Room ' + room.room_number +
                    ' - ' + room.room_type +
                    ' - $' + priceText + '/night';

                const option = new Option(
                    roomLabel,
                    String(room.id)
                );

                // Used by existing booking calculator.
                option.dataset.price = String(
                    room.price_per_night
                );

                roomSelect.add(option);
            });

            // Enable room selection.
            roomSelect.disabled = false;

            // ==================================
            // RESTORE PREVIOUS SELECTION
            // ==================================

            const stillAvailable = rooms.some(
                function (room) {
                    return String(room.id) ===
                        selectedRoomId;
                }
            );

            roomSelect.value = stillAvailable
                ? selectedRoomId
                : '';

            // Refresh estimated price.
            updatePrice();

            // ==================================
            // SUCCESS MESSAGE
            // ==================================

            setMessage(
                rooms.length +
                ' room(s) available for the selected dates.'
            );

        } catch (error) {

            if (error.name === 'AbortError') {
                return;
            }

            if (thisRequest !== requestNumber) {
                return;
            }

            console.error(
                'PITO HOTEL Room Availability:',
                error
            );

            clearRooms('Unable to load rooms');

            setMessage(
                'Could not check available rooms. ' +
                'Please try again.',
                true
            );

        } finally {

            if (thisRequest === requestNumber) {
                currentRequest = null;
            }
        }
    }

    // ==========================================
    // DATE CHANGE EVENTS
    // ==========================================

    checkIn.addEventListener(
        'change',
        loadAvailableRooms
    );

    checkOut.addEventListener(
        'change',
        loadAvailableRooms
    );

    // ==========================================
    // OPEN NEW BOOKING POPUP
    // ==========================================

    if (bookingDialog) {

        const observer = new MutationObserver(
            function () {

                if (bookingDialog.open) {
                    loadAvailableRooms();
                }
            }
        );

        observer.observe(bookingDialog, {
            attributes: true,
            attributeFilter: ['open']
        });
    }

    // ==========================================
    // RESTORE AFTER VALIDATION ERROR
    // ==========================================

    // Existing index.js restores the dates
    // from rvOldState when validation fails.
    // Load available rooms after restoration.
    loadAvailableRooms();

});
