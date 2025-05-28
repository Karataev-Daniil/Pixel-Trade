document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('product_gallery_input');
    const galleryContainer = document.getElementById('gallery_preview');
    const removeInput = document.getElementById('remove_gallery_ids_input');
    const orderInput = document.getElementById('gallery_order_input');
    const mainThumbInput = document.getElementById('main_thumbnail_id');
    const MAX_IMAGES = 6;

    if (!input || !galleryContainer || !removeInput || !orderInput || !mainThumbInput) return;

    window.checkGalleryLimit = function (input) {
        if (!input.files || input.files.length === 0) return;

        const existingCount = galleryContainer.querySelectorAll('.gallery-item').length;
        const files = Array.from(input.files);

        if (existingCount >= MAX_IMAGES) {
            alert('Максимум 6 изображений!');
            input.value = '';
            return;
        }

        if (files.length + existingCount > MAX_IMAGES) {
            alert('Можно добавить только ' + (MAX_IMAGES - existingCount) + ' изображений!');
            input.value = '';
            return;
        }

        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const imgSrc = e.target.result;
                const uniqueId = 'new-' + Date.now() + '-' + index;

                const item = document.createElement('div');
                item.classList.add('gallery-item');
                item.dataset.id = uniqueId;

                item.innerHTML = `
                    <img src="${imgSrc}" alt="preview" />
                    <span class="gallery-remove link-small-default" title="Удалить">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </span>
                    <button type="button" class="set-thumbnail-btn" title="Сделать миниатюрой">★</button>
                    <input type="hidden" name="existing_gallery_ids[]" value="${uniqueId}">
                `;

                galleryContainer.appendChild(item);
                updateGalleryOrder();
                updateThumbnailSelection();
            };

            reader.readAsDataURL(file);
        });

        input.value = '';
    };

    galleryContainer.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.gallery-remove');
        if (removeBtn) {
            const item = removeBtn.closest('.gallery-item');
            const id = item.dataset.id;

            if (id && !id.startsWith('new-')) {
                let ids = removeInput.value ? removeInput.value.split(',') : [];
                ids.push(id);
                removeInput.value = [...new Set(ids)].join(',');
            }

            item.remove();
            updateGalleryOrder();
            updateThumbnailSelection();
        }

        const thumbnailBtn = e.target.closest('.set-thumbnail-btn');
        if (thumbnailBtn) {
            const allItems = galleryContainer.querySelectorAll('.gallery-item');
            allItems.forEach(item => item.classList.remove('thumbnail'));

            const selectedItem = thumbnailBtn.closest('.gallery-item');
            selectedItem.classList.add('thumbnail');

            if (mainThumbInput) {
                mainThumbInput.value = selectedItem.dataset.id;
            }

            updateGalleryOrder();
        }
    });

    if (galleryContainer) {
        Sortable.create(galleryContainer, {
            animation: 150,
            onEnd: function () {
                updateGalleryOrder();
                updateThumbnailSelection();
            }
        });
    }

    function updateGalleryOrder() {
        const order = [];
        galleryContainer.querySelectorAll('.gallery-item').forEach(item => {
            order.push(item.dataset.id);
        });
        if (orderInput) {
            orderInput.value = order.join(',');
        }
    }

    function updateThumbnailSelection() {
        const items = galleryContainer.querySelectorAll('.gallery-item');
        const currentThumb = galleryContainer.querySelector('.gallery-item.thumbnail');

        if (!currentThumb && items.length > 0) {
            items.forEach(item => item.classList.remove('thumbnail'));
            items[0].classList.add('thumbnail');
            if (mainThumbInput) {
                mainThumbInput.value = items[0].dataset.id;
            }
        }

        if (items.length === 0 && mainThumbInput) {
            mainThumbInput.value = '';
        }
    }

    updateGalleryOrder();
    updateThumbnailSelection();
});
