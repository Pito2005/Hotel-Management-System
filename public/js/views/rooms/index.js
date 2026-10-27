
/* =========================================
   PITO HOTEL - ROOM MANAGEMENT
   Add, Manage, Delete, Auto Price, Photos
========================================= */

document.addEventListener('DOMContentLoaded', () => {
    const $ = (id) => document.getElementById(id);

    const roomDialog = $('roomDialog');
    const typeDialog = $('typeDialog');
    const deleteDialog = $('deleteDialog');

    // Staff cannot access admin management dialogs
    if (!roomDialog || !typeDialog || !deleteDialog) return;

    const roomForm = $('roomForm');
    const typeForm = $('typeForm');
    const deleteForm = $('deleteForm');

    const getUrl = (template, id) =>
        template.replace(/__ROOM_ID__|__TYPE_ID__/g, encodeURIComponent(id));

    // ======================================
    // AUTO PRICE FROM ROOM TYPE
    // ======================================

    function updateRoomPrice() {
        const select = $('roomTypeId');
        const option = select.selectedOptions[0];
        const price = option?.dataset.price;

        $('roomPrice').value =
            price !== undefined && price !== ''
                ? '$' + Number(price).toFixed(2)
                : '';
    }

    $('roomTypeId').addEventListener('change', updateRoomPrice);

    // ======================================
    // OPEN ADD / MANAGE ROOM
    // ======================================

    function openRoom(data = null) {
        const editing = Boolean(data?.id);

        roomForm.reset();

        roomForm.action = editing
            ? (data.updateUrl ||
                getUrl(roomForm.dataset.updateTemplate, data.id))
            : roomForm.dataset.storeUrl;

        $('roomMethod').disabled = !editing;
        $('roomKind').value = editing ? 'room_edit' : 'room_create';
        $('roomRecordId').value = data?.id || '';

        $('roomDialogTitle').textContent = editing
            ? 'Manage Room #' + (data.number || '')
            : 'Add New Room';

        $('roomNumber').value = data?.number || '';
        $('roomTypeId').value = data?.type || '';
        $('roomFloor').value = data?.floor ?? '';
        $('roomStatus').value = data?.status || 'available';
        $('roomDescription').value = data?.description || '';

        updateRoomPrice();

        // Previous room picture
        const preview = $('roomPhotoPreview');
        preview.hidden = !data?.image;

        if (data?.image) {
            preview.src = data.image;
        } else {
            preview.removeAttribute('src');
        }

        // Delete is available inside Edit popup only
        const deleteButton = $('roomDeleteButton');

        deleteButton.hidden = !editing;

        deleteButton.dataset.url = editing
            ? (data.deleteUrl ||
                getUrl(roomForm.dataset.deleteTemplate, data.id))
            : '';

        deleteButton.dataset.label =
            'Room #' + (data?.number || '');

        roomDialog.showModal();
    }

    // ======================================
    // OPEN ADD / MANAGE ROOM TYPE
    // ======================================

    function openType(data = null) {
        const editing = Boolean(data?.id);

        typeForm.reset();

        typeForm.action = editing
            ? (data.updateUrl ||
                getUrl(typeForm.dataset.updateTemplate, data.id))
            : typeForm.dataset.storeUrl;

        $('typeMethod').disabled = !editing;
        $('typeKind').value = editing ? 'type_edit' : 'type_create';
        $('typeRecordId').value = data?.id || '';

        $('typeDialogTitle').textContent = editing
            ? 'Manage Room Type'
            : 'Add Room Type';

        $('typeName').value = data?.name || '';
        $('typeBasePrice').value = data?.price ?? '';
        $('typeDescription').value = data?.description || '';

        const deleteButton = $('typeDeleteButton');

        deleteButton.hidden = !editing;

        deleteButton.dataset.url = editing
            ? (data.deleteUrl ||
                getUrl(typeForm.dataset.deleteTemplate, data.id))
            : '';

        deleteButton.dataset.label =
            'Room Type: ' + (data?.name || '');

        typeDialog.showModal();
    }

    // ======================================
    // ADD BUTTONS
    // ======================================

    document.querySelectorAll('[data-open-room]').forEach(button => {
        button.addEventListener('click', () => openRoom());
    });

    document.querySelectorAll('[data-open-type]').forEach(button => {
        button.addEventListener('click', () => openType());
    });

    // ======================================
    // MANAGE ROOM BUTTONS
    // ======================================

    document.querySelectorAll('[data-room-manage]').forEach(button => {
        button.addEventListener('click', () => {
            const d = button.dataset;

            openRoom({
                id: d.id,
                number: d.number,
                type: d.type,
                floor: d.floor,
                status: d.status,
                description: d.description,
                image: d.image,
                updateUrl: d.updateUrl,
                deleteUrl: d.deleteUrl
            });
        });
    });

    // ======================================
    // MANAGE ROOM TYPE BUTTONS
    // ======================================

    document.querySelectorAll('[data-type-manage]').forEach(button => {
        button.addEventListener('click', () => {
            const d = button.dataset;

            openType({
                id: d.id,
                name: d.name,
                price: d.price,
                description: d.description,
                updateUrl: d.updateUrl,
                deleteUrl: d.deleteUrl
            });
        });
    });

    // ======================================
    // DELETE CONFIRMATION
    // ======================================

    function openDelete(url, label) {
        deleteForm.action = url;
        $('deleteItemName').textContent = label;
        deleteDialog.showModal();
    }

    $('roomDeleteButton').addEventListener('click', function () {
        const url = this.dataset.url;
        const label = this.dataset.label;

        roomDialog.close();
        openDelete(url, label);
    });

    $('typeDeleteButton').addEventListener('click', function () {
        const url = this.dataset.url;
        const label = this.dataset.label;

        typeDialog.close();
        openDelete(url, label);
    });

    // ======================================
    // CLOSE BUTTONS
    // ======================================

    document.querySelectorAll('[data-close-dialog]').forEach(button => {
        button.addEventListener('click', () => {
            $(button.dataset.closeDialog)?.close();
        });
    });

    // ======================================
    // IMAGE PREVIEW
    // ======================================

    $('roomPhoto').addEventListener('change', function () {
        const file = this.files[0];
        const preview = $('roomPhotoPreview');

        if (!file) return;

        const imageUrl = URL.createObjectURL(file);

        preview.onload = () => URL.revokeObjectURL(imageUrl);
        preview.onerror = () => URL.revokeObjectURL(imageUrl);
        preview.src = imageUrl;
        preview.hidden = false;
    });

    // ======================================
    // RESTORE POPUP AFTER VALIDATION ERRORS
    // ======================================

    const oldState = $('rmOldState');

    if (oldState) {
        const d = oldState.dataset;

        if (d.kind === 'room_create' || d.kind === 'room_edit') {
            openRoom(d.kind === 'room_edit' ? {
                id: d.id,
                number: d.number,
                type: d.roomType,
                floor: d.floor,
                status: d.status,
                description: d.description
            } : null);

            $('roomNumber').value = d.number || '';
            $('roomTypeId').value = d.roomType || '';
            $('roomFloor').value = d.floor || '';
            $('roomStatus').value = d.status || 'available';
            $('roomDescription').value = d.description || '';

            updateRoomPrice();
        }

        if (d.kind === 'type_create' || d.kind === 'type_edit') {
            openType(d.kind === 'type_edit' ? {
                id: d.id,
                name: d.name,
                price: d.basePrice,
                description: d.description
            } : null);

            $('typeName').value = d.name || '';
            $('typeBasePrice').value = d.basePrice || '';
            $('typeDescription').value = d.description || '';
        }
    }
});
