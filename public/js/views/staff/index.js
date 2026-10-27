
/* PITO HOTEL - STAFF MANAGEMENT POPUPS */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const $ = id => document.getElementById(id);

    const staffDialog = $('smStaffDialog');
    const deleteDialog = $('smDeleteDialog');
    const form = $('smStaffForm');
    const method = $('smMethod');
    const kind = $('smFormKind');
    const recordId = $('smRecordId');
    const title = $('smDialogTitle');

    const nameInput = $('smName');
    const emailInput = $('smEmail');
    const passwordInput = $('smPassword');
    const confirmationInput = $('smPasswordConfirmation');

    const passwordRequired = $('smPasswordRequired');
    const confirmRequired = $('smConfirmRequired');
    const passwordHint = $('smPasswordHint');

    const photoInput = $('smPhoto');
    const photoPreview = $('smPhotoPreview');

    const deleteButton = $('smDeleteButton');
    const deleteForm = $('smDeleteForm');
    const deleteName = $('smDeleteName');

    if (!staffDialog || !form) return;

    function show(dialog) {
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    }

    // =====================================
    // PHOTO PREVIEW
    // =====================================

    function showPhoto(src) {
        if (src) {
            photoPreview.src = src;
            photoPreview.hidden = false;
        } else {
            photoPreview.removeAttribute('src');
            photoPreview.hidden = true;
        }
    }

    // =====================================
    // ADD STAFF
    // =====================================

    function openCreate() {
        form.reset();

        form.action = form.dataset.storeUrl;
        method.disabled = true;

        kind.value = 'staff_create';
        recordId.value = '';

        title.textContent = 'Add Staff';

        passwordInput.required = true;
        confirmationInput.required = true;

        passwordRequired.hidden = false;
        confirmRequired.hidden = false;

        passwordHint.textContent = 'At least 8 characters.';

        deleteButton.hidden = true;
        deleteButton.dataset.url = '';

        showPhoto('');
        show(staffDialog);
    }

    // =====================================
    // MANAGE STAFF
    // =====================================

    function openEdit(button) {
        form.reset();

        form.action = button.dataset.updateUrl;
        method.disabled = false;

        kind.value = 'staff_update';
        recordId.value = button.dataset.id || '';

        title.textContent = 'Manage Staff';

        nameInput.value = button.dataset.name || '';
        emailInput.value = button.dataset.email || '';

        passwordInput.value = '';
        confirmationInput.value = '';

        passwordInput.required = false;
        confirmationInput.required = false;

        passwordRequired.hidden = true;
        confirmRequired.hidden = true;

        passwordHint.textContent =
            'Leave blank to keep the current password.';

        showPhoto(button.dataset.photoUrl || '');

        const bookingsCount = Number(
            button.dataset.bookingsCount || 0
        );

        const canDelete =
            bookingsCount === 0 &&
            Boolean(button.dataset.deleteUrl);

        deleteButton.hidden = !canDelete;

        deleteButton.dataset.url =
            button.dataset.deleteUrl || '';

        deleteButton.dataset.name =
            button.dataset.name || 'Selected staff';

        show(staffDialog);
    }

    // =====================================
    // OPEN BUTTONS
    // =====================================

    $('smAddStaff')?.addEventListener(
        'click',
        openCreate
    );

    document.querySelectorAll('[data-sm-manage]')
        .forEach(button => {
            button.addEventListener('click', () => {
                openEdit(button);
            });
        });

    // =====================================
    // CLOSE BUTTONS
    // =====================================

    document.querySelectorAll('[data-sm-close]')
        .forEach(button => {
            button.addEventListener('click', () => {
                const dialog = $(
                    button.dataset.smClose
                );

                if (dialog?.open) {
                    dialog.close();
                }
            });
        });

    // =====================================
    // SHOW NEW PROFILE PICTURE
    // =====================================

    photoInput?.addEventListener('change', function () {
        const file = this.files?.[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = () => {
            showPhoto(String(reader.result || ''));
        };

        reader.readAsDataURL(file);
    });

    // =====================================
    // DELETE CONFIRMATION POPUP
    // =====================================

    deleteButton?.addEventListener('click', function () {
        const url = this.dataset.url;

        if (!url || !deleteForm) return;

        deleteForm.action = url;

        deleteName.textContent =
            this.dataset.name || 'Selected staff';

        if (staffDialog.open) {
            staffDialog.close();
        }

        show(deleteDialog);
    });

    // =====================================
    // RESTORE AFTER VALIDATION ERRORS
    // =====================================

    const old = $('smOldState');

    if (old) {
        const data = old.dataset;

        if (data.kind === 'staff_create') {
            openCreate();

            nameInput.value = data.name || '';
            emailInput.value = data.email || '';

        } else if (data.kind === 'staff_update') {
            const button = [
                ...document.querySelectorAll('[data-sm-manage]')
            ].find(item => item.dataset.id === data.id);

            if (button) {
                openEdit(button);

                nameInput.value = data.name || '';
                emailInput.value = data.email || '';
            }
        }

    } else {
        // Support old staff.create and staff.edit links
        const params = new URLSearchParams(
            window.location.search
        );

        if (params.get('new') === '1') {
            openCreate();
        }

        if (params.has('edit')) {
            const button = [
                ...document.querySelectorAll('[data-sm-manage]')
            ].find(
                item => item.dataset.id === params.get('edit')
            );

            if (button) {
                openEdit(button);
            }
        }
    }
});
