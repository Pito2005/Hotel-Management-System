
/* =========================================
   PITO HOTEL - DELETE BOOKING
   ADMIN ONLY
========================================= */

document.addEventListener('DOMContentLoaded', function () {

    const bookingDialog =
        document.getElementById('bookingDialog');

    const deleteButton =
        document.getElementById('deleteBookingButton');

    const deleteDialog =
        document.getElementById('deleteBookingDialog');

    const deleteForm =
        document.getElementById('deleteBookingForm');

    const deleteName =
        document.getElementById('deleteBookingName');

    if (
        !deleteButton ||
        !deleteDialog ||
        !deleteForm ||
        !deleteName
    ) {
        return;
    }

    // Select booking to delete
    document.querySelectorAll('[data-booking-manage]')
        .forEach(function (manageButton) {

            manageButton.addEventListener('click', function () {

                const deleteUrl =
                    manageButton.dataset.deleteUrl || '';

                const isPaid =
                    manageButton.dataset.paymentStatus === 'paid';

                deleteButton.hidden = !deleteUrl || isPaid;

                deleteButton.dataset.deleteUrl = deleteUrl;

                deleteButton.dataset.bookingName =
                    'Booking #' +
                    (manageButton.dataset.id || '') +
                    ' - ' +
                    (manageButton.dataset.guest || 'Guest');
            });
        });

    // Open delete confirmation
    deleteButton.addEventListener('click', function () {

        const url = deleteButton.dataset.deleteUrl;

        if (!url) return;

        deleteForm.action = url;

        deleteName.textContent =
            deleteButton.dataset.bookingName ||
            'Selected Booking';

        if (bookingDialog && bookingDialog.open) {
            bookingDialog.close();
        }

        if (!deleteDialog.open) {
            deleteDialog.showModal();
        }
    });

    // Close confirmation popup
    deleteDialog
        .querySelectorAll(
            '[data-close-dialog="deleteBookingDialog"]'
        )
        .forEach(function (button) {

            button.addEventListener('click', function () {

                if (deleteDialog.open) {
                    deleteDialog.close();
                }
            });
        });
});
