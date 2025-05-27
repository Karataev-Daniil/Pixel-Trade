function checkGalleryLimit(input) {
    const max = 6;
    const existing = document.querySelectorAll('#gallery_preview .gallery-item').length;

    if (input.files.length + existing > max) {
        alert('Максимум 6 изображений!');
        input.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const galleryContainer = document.getElementById('gallery_preview');
    if (galleryContainer) {
        Sortable.create(galleryContainer, {
            animation: 150,
            onEnd: updateGalleryOrder
        });
    }

    function updateGalleryOrder() {
        const order = [];
        document.querySelectorAll('#gallery_preview .gallery-item').forEach(item => {
            order.push(item.dataset.id);
        });
        const orderInput = document.getElementById('gallery_order_input');
        if (orderInput) {
            orderInput.value = order.join(',');
        }
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('gallery-remove')) {
            const item = e.target.closest('.gallery-item');
            const id = item.dataset.id;

            const removeInput = document.getElementById('remove_gallery_ids_input');
            let ids = removeInput.value ? removeInput.value.split(',') : [];
            ids.push(id);
            removeInput.value = [...new Set(ids)].join(',');

            item.remove();
            updateGalleryOrder();
        }
    });
});
