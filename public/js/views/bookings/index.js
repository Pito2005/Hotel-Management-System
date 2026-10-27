
/* ==================================================
   PITO HOTEL - RESERVATION MANAGEMENT
   Full JavaScript
   Guests + Bookings + Receipt Print + PDF
================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // 1. GET ELEMENTS
    // ==========================================

    const $ = (id) => document.getElementById(id);

    const guestDialog = $('guestDialog');
    const newBookingDialog = $('newBookingDialog');
    const bookingDialog = $('bookingDialog');
    const receiptDialog = $('receiptDialog');
    const deleteGuestDialog = $('deleteGuestDialog');

    const guestForm = $('guestForm');
    const newBookingForm = $('newBookingForm');
    const bookingUpdateForm = $('bookingUpdateForm');

    if (
        !guestDialog ||
        !newBookingDialog ||
        !bookingDialog ||
        !receiptDialog ||
        !guestForm ||
        !newBookingForm ||
        !bookingUpdateForm
    ) {
        console.warn(
            'PITO HOTEL: Reservation Management elements missing.'
        );
        return;
    }

    // ==========================================
    // 2. HELPER FUNCTIONS
    // ==========================================

    function makeUrl(template, id) {
        return (template || '').replace(
            /__GUEST_ID__|__BOOKING_ID__/g,
            encodeURIComponent(String(id))
        );
    }

    function showDialog(dialog) {
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    }

    function closeDialog(dialog) {
        if (dialog && dialog.open) {
            dialog.close();
        }
    }

    function formatMoney(amount) {
        const value = Number(amount);

        if (!Number.isFinite(value)) {
            return '$0.00';
        }

        return '$' + value.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // ==========================================
    // 3. ADD / EDIT GUEST
    // ==========================================

    function openGuest(data = null) {

        const editing = Boolean(data && data.id);

        guestForm.reset();

        if (editing) {
            guestForm.action = data.updateUrl ||
                makeUrl(
                    guestForm.dataset.updateTemplate,
                    data.id
                );
        } else {
            guestForm.action = guestForm.dataset.storeUrl;
        }

        // Enable PUT only when editing
        $('guestMethod').disabled = !editing;

        $('guestKind').value = editing
            ? 'guest_edit'
            : 'guest_create';

        $('guestRecordId').value = editing
            ? data.id
            : '';

        $('guestDialogTitle').textContent = editing
            ? 'Manage Guest'
            : 'Add New Guest';

        // Fill guest information
        $('guestName').value = data?.name || '';
        $('guestPhone').value = data?.phone || '';
        $('guestEmail').value = data?.email || '';
        $('guestNationality').value =
            data?.nationality || '';
        $('guestAddress').value = data?.address || '';

        // Admin delete button
        const deleteButton = $('guestDeleteButton');

        if (deleteButton) {

            let deleteUrl = '';

            if (editing) {
                deleteUrl = data.deleteUrl ||
                    makeUrl(
                        guestForm.dataset.deleteTemplate,
                        data.id
                    );
            }

            deleteButton.hidden = !editing || !deleteUrl;

            deleteButton.dataset.url = deleteUrl;
            deleteButton.dataset.name = data?.name || '';
        }

        showDialog(guestDialog);
    }

    // Add Guest button
    document.querySelectorAll('[data-add-guest]')
        .forEach(function (button) {

            button.addEventListener('click', function () {
                openGuest();
            });

        });

    // Manage Guest button
    document.querySelectorAll('[data-guest-manage]')
        .forEach(function (button) {

            button.addEventListener('click', function () {
                openGuest(button.dataset);
            });

        });

    // ==========================================
    // 4. DELETE GUEST CONFIRMATION
    // ==========================================

    const guestDeleteButton = $('guestDeleteButton');

    if (guestDeleteButton && deleteGuestDialog) {

        guestDeleteButton.addEventListener(
            'click',
            function () {

                const deleteUrl = this.dataset.url;
                const guestName = this.dataset.name;

                if (!deleteUrl) {
                    return;
                }

                const deleteForm = $('deleteGuestForm');

                deleteForm.action = deleteUrl;

                $('deleteGuestName').textContent =
                    guestName || 'Selected Guest';

                closeDialog(guestDialog);

                showDialog(deleteGuestDialog);
            }
        );
    }

    // ==========================================
    // 5. NEW BOOKING POPUP
    // ==========================================

    const bookingCustomerName = $('bookingCustomerName');
    const bookingCustomerPhone = $('bookingCustomerPhone');

    const bookingRoom = $('bookingRoom');
    const bookingCheckIn = $('bookingCheckIn');
    const bookingCheckOut = $('bookingCheckOut');
    const bookingAdults = $('bookingAdults');
    const bookingNotes = $('bookingNotes');

    function openNewBooking() {

        // Reset form whenever New Booking is opened
        newBookingForm.reset();

        if (bookingAdults) {
            bookingAdults.value = 1;
        }

        if (bookingCheckOut) {
            bookingCheckOut.removeAttribute('min');
        }

        calculateEstimate();

        showDialog(newBookingDialog);
    }

    // New Booking button
    document.querySelectorAll('[data-add-booking]')
        .forEach(function (button) {

            button.addEventListener('click', function () {
                openNewBooking();
            });

        });

    // ==========================================
    // 6. AUTOMATIC BOOKING PRICE
    // ==========================================

    function parseDateUTC(value) {

        if (!value) {
            return null;
        }

        const parts = value.split('-').map(Number);

        if (
            parts.length !== 3 ||
            parts.some(Number.isNaN)
        ) {
            return null;
        }

        const year = parts[0];
        const month = parts[1];
        const day = parts[2];

        const date = new Date(
            Date.UTC(year, month - 1, day)
        );

        if (
            date.getUTCFullYear() !== year ||
            date.getUTCMonth() !== month - 1 ||
            date.getUTCDate() !== day
        ) {
            return null;
        }

        return date.getTime();
    }

    function calculateEstimate() {

        if (
            !bookingRoom ||
            !bookingCheckIn ||
            !bookingCheckOut
        ) {
            return;
        }

        const selectedOption =
            bookingRoom.selectedOptions[0];

        const hasRoom = Boolean(selectedOption?.value);

        const roomPrice = Number(
            selectedOption?.dataset.price || 0
        );

        const start = parseDateUTC(
            bookingCheckIn.value
        );

        const end = parseDateUTC(
            bookingCheckOut.value
        );

        let nights = 0;

        if (start !== null && end !== null) {
            nights = Math.round(
                (end - start) / 86400000
            );
        }

        const valid =
            hasRoom &&
            Number.isFinite(roomPrice) &&
            roomPrice >= 0 &&
            nights > 0;

        const total = valid
            ? roomPrice * nights
            : 0;

        $('bookingEstimate').textContent =
            formatMoney(total);

        $('bookingNights').textContent = valid
            ? nights + ' night(s) × ' +
            formatMoney(roomPrice) + '/night'
            : 'Select a room and valid dates';
    }

    // ==========================================
    // 7. AUTOMATIC CHECK-OUT DATE
    // ==========================================

    function updateCheckoutMin() {

        if (!bookingCheckIn || !bookingCheckOut) {
            return;
        }

        if (bookingCheckIn.value) {

            const date = new Date(
                bookingCheckIn.value + 'T00:00:00Z'
            );

            if (Number.isFinite(date.getTime())) {

                date.setUTCDate(
                    date.getUTCDate() + 1
                );

                const minDate =
                    date.toISOString().slice(0, 10);

                bookingCheckOut.min = minDate;

                // Clear checkout if it is too early
                if (
                    bookingCheckOut.value &&
                    bookingCheckOut.value < minDate
                ) {
                    bookingCheckOut.value = '';
                }
            }

        } else {
            bookingCheckOut.removeAttribute('min');
        }

        calculateEstimate();
    }

    if (bookingRoom) {
        bookingRoom.addEventListener(
            'change',
            calculateEstimate
        );
    }

    if (bookingCheckIn) {
        bookingCheckIn.addEventListener(
            'change',
            updateCheckoutMin
        );
    }

    if (bookingCheckOut) {
        bookingCheckOut.addEventListener(
            'change',
            calculateEstimate
        );
    }

    // ==========================================
    // 8. MANAGE EXISTING BOOKING
    // ==========================================

    function openBooking(data) {

        if (!data || !data.id) {
            return;
        }

        const updateUrl = data.updateUrl ||
            makeUrl(
                bookingUpdateForm.dataset.updateTemplate,
                data.id
            );

        bookingUpdateForm.action = updateUrl;

        $('bookingRecordId').value = data.id;

        $('bookingDialogTitle').textContent =
            'Manage Booking #' + data.id;

        // Booking summary
        $('bookingSummaryGuest').textContent =
            data.guest || 'Unknown Guest';

        $('bookingSummaryRoom').textContent =
            'Room ' + (data.room || '-');

        $('bookingSummaryTotal').textContent =
            formatMoney(data.total || 0);

        // Current booking status
        $('editBookingStatus').value =
            data.status || 'confirmed';

        // Current payment status
        $('editPaymentStatus').value =
            data.paymentStatus || 'unpaid';

        // Current payment method
        const editPaymentMethod =
            $('editPaymentMethod');

        if (editPaymentMethod) {
            editPaymentMethod.value =
                data.paymentMethod || 'unspecified';
        }

        // Receipt PDF link
        const receiptLink =
            $('bookingReceiptLink');

        const downloadUrl =
            data.downloadUrl || '';

        receiptLink.href = downloadUrl || '#';

        if (downloadUrl) {
            receiptLink.removeAttribute('aria-disabled');
            receiptLink.style.pointerEvents = '';
            receiptLink.style.opacity = '';
        } else {
            receiptLink.setAttribute('aria-disabled', 'true');
            receiptLink.style.pointerEvents = 'none';
            receiptLink.style.opacity = '.5';
        }

        // Receipt Print Preview URLs
        const bookingPrintButton =
            $('bookingPrintButton');

        bookingPrintButton.dataset.previewUrl =
            data.previewUrl || '';

        bookingPrintButton.dataset.downloadUrl =
            downloadUrl;

        bookingPrintButton.disabled =
            !data.previewUrl;

        showDialog(bookingDialog);


        // ==========================================
        // ADMIN DELETE BOOKING BUTTON
        // ==========================================

        const deleteBookingButton = $('deleteBookingButton');

        if (deleteBookingButton) {
            const canDelete =
                Boolean(data.deleteUrl) &&
                data.paymentStatus !== 'paid';

            deleteBookingButton.hidden = !canDelete;

            deleteBookingButton.dataset.url =
                data.deleteUrl || '';

            deleteBookingButton.dataset.name =
                'Booking #' + data.id + ' — ' +
                (data.guest || 'Guest');
        }

    }

    // Manage Booking button
    document.querySelectorAll('[data-booking-manage]')
        .forEach(function (button) {

            button.addEventListener('click', function () {
                openBooking(button.dataset);
            });

        });

    // ==========================================
    // 9. RECEIPT PREVIEW POPUP
    // ==========================================

    const receiptFrame = $('receiptFrame');
    const receiptPrintNow = $('receiptPrintNow');
    const receiptDownloadLink =
        $('receiptDownloadLink');

    function openReceiptPreview(
        previewUrl,
        downloadUrl
    ) {

        if (!previewUrl) {
            return;
        }

        // Close Manage Booking before showing Receipt
        closeDialog(bookingDialog);

        // Disable print until receipt is loaded
        receiptPrintNow.disabled = true;

        receiptDownloadLink.href =
            downloadUrl || '#';

        showDialog(receiptDialog);

        // Load receipt HTML in iframe
        receiptFrame.src = previewUrl;
    }

    // Print directly from Booking Table
    document.querySelectorAll('[data-print-receipt]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                openReceiptPreview(
                    button.dataset.previewUrl,
                    button.dataset.downloadUrl
                );

            });

        });

    // Print from Manage Booking Popup
    const bookingPrintButton =
        $('bookingPrintButton');

    if (bookingPrintButton) {

        bookingPrintButton.addEventListener(
            'click',
            function () {

                openReceiptPreview(
                    this.dataset.previewUrl,
                    this.dataset.downloadUrl
                );

            }
        );
    }

    // ==========================================
    // 10. ENABLE PRINT AFTER RECEIPT LOADS
    // ==========================================

    if (receiptFrame && receiptPrintNow) {

        receiptFrame.addEventListener(
            'load',
            function () {

                const hasPreview =
                    receiptFrame.getAttribute('src');

                receiptPrintNow.disabled = !(
                    receiptDialog.open &&
                    hasPreview
                );
            }
        );
    }

    // ==========================================
    // 11. PRINT RECEIPT
    // ==========================================

    if (receiptPrintNow) {

        receiptPrintNow.addEventListener(
            'click',
            function () {

                if (this.disabled) {
                    return;
                }

                try {

                    const printWindow =
                        receiptFrame.contentWindow;

                    if (!printWindow) {
                        throw new Error(
                            'Receipt preview is unavailable.'
                        );
                    }

                    printWindow.focus();

                    // Open browser printer dialog
                    printWindow.print();

                } catch (error) {

                    // Fallback: open receipt in new tab
                    const previewUrl =
                        receiptFrame.getAttribute('src');

                    if (previewUrl) {
                        window.open(
                            previewUrl,
                            '_blank',
                            'noopener'
                        );
                    }
                }
            }
        );
    }

    // ==========================================
    // 12. CLEAR RECEIPT WHEN POPUP CLOSES
    // ==========================================

    if (receiptDialog && receiptFrame) {

        receiptDialog.addEventListener(
            'close',
            function () {

                receiptFrame.removeAttribute('src');

                if (receiptPrintNow) {
                    receiptPrintNow.disabled = true;
                }
            }
        );
    }

    // ==========================================
    // 13. CLOSE ALL POPUPS
    // ==========================================

    document.querySelectorAll('[data-close-dialog]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const dialogId =
                    button.dataset.closeDialog;

                const dialog = $(dialogId);

                closeDialog(dialog);
            });

        });

    // ==========================================
    // 14. RESTORE FORMS AFTER VALIDATION ERROR
    // ==========================================

    const oldState = $('rvOldState');

    let restoredPopup = false;

    if (oldState) {

        const d = oldState.dataset;

        // ======================================
        // RESTORE GUEST FORM
        // ======================================

        if (
            d.kind === 'guest_create' ||
            d.kind === 'guest_edit'
        ) {

            let guestData = null;

            if (d.kind === 'guest_edit' && d.id) {

                const guestButton = Array.from(
                    document.querySelectorAll(
                        '[data-guest-manage]'
                    )
                ).find(function (button) {
                    return button.dataset.id === d.id;
                });

                guestData = guestButton?.dataset || {
                    id: d.id,
                    name: d.name,
                    phone: d.phone,
                    email: d.email,
                    nationality: d.nationality,
                    address: d.address
                };
            }

            openGuest(guestData);

            $('guestName').value = d.name || '';
            $('guestPhone').value = d.phone || '';
            $('guestEmail').value = d.email || '';

            $('guestNationality').value =
                d.nationality || '';

            $('guestAddress').value =
                d.address || '';

            restoredPopup = true;
        }

        // ======================================
        // RESTORE NEW BOOKING FORM
        // WITH CUSTOMER NAME + PHONE
        // ======================================

        if (d.kind === 'booking_create') {

            openNewBooking();

            // New customer fields
            if (bookingCustomerName) {
                bookingCustomerName.value =
                    d.customerName || '';
            }

            if (bookingCustomerPhone) {
                bookingCustomerPhone.value =
                    d.customerPhone || '';
            }

            // Room and booking dates
            if (bookingRoom) {
                bookingRoom.value =
                    d.roomId || '';
            }

            if (bookingCheckIn) {
                bookingCheckIn.value =
                    d.checkIn || '';
            }

            if (bookingCheckOut) {
                bookingCheckOut.value =
                    d.checkOut || '';
            }

            if (bookingAdults) {
                bookingAdults.value =
                    d.adults || '1';
            }

            if (bookingNotes) {
                bookingNotes.value =
                    d.notes || '';
            }

            // Restore payment method
            const newPaymentMethod =
                $('newPaymentMethod');

            if (newPaymentMethod) {
                newPaymentMethod.value =
                    d.paymentMethod || 'unspecified';
            }

            updateCheckoutMin();

            restoredPopup = true;
        }

        // ======================================
        // RESTORE MANAGE BOOKING FORM
        // ======================================

        if (
            d.kind === 'booking_update' &&
            d.id
        ) {

            const bookingButton = Array.from(
                document.querySelectorAll(
                    '[data-booking-manage]'
                )
            ).find(function (button) {
                return button.dataset.id === d.id;
            });

            const bookingData =
                bookingButton?.dataset || {
                    id: d.id,
                    status: d.status,
                    paymentStatus: d.paymentStatus,
                    paymentMethod: d.paymentMethod
                };

            openBooking(bookingData);

            $('editBookingStatus').value =
                d.status || 'confirmed';

            $('editPaymentStatus').value =
                d.paymentStatus || 'unpaid';

            const editPaymentMethod =
                $('editPaymentMethod');

            if (editPaymentMethod) {
                editPaymentMethod.value =
                    d.paymentMethod || 'unspecified';
            }

            restoredPopup = true;
        }
    }

    // ==========================================
    // 15. OLD NEW BOOKING URL SUPPORT
    // ==========================================

    const params = new URLSearchParams(
        window.location.search
    );

    if (
        !restoredPopup &&
        params.get('new_booking') === '1' &&
        !newBookingDialog.open
    ) {
        openNewBooking();
    }

    // ==========================================
    // READY
    // ==========================================

    console.log(
        'PITO HOTEL Reservation Management loaded successfully.'
    );

});
