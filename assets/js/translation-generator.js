function generateTranslations() {
    const title = document.querySelector('input[name="product_title"]').value;
    const description = document.querySelector('textarea[name="product_content"]').value;
    const titleEn = document.querySelector('input[name="title_en"]').value.trim();
    const titleRo = document.querySelector('input[name="title_ro"]').value.trim();
    const descEn = document.querySelector('textarea[name="description_en"]').value.trim();
    const descRo = document.querySelector('textarea[name="description_ro"]').value.trim();
    const messageBlock = document.getElementById('translation-message');

    if (titleEn && titleRo && descEn && descRo) {
        messageBlock.style.color = 'orange';
        messageBlock.textContent = 'Переводы уже заполнены.';
        return;
    }

    messageBlock.style.color = 'black';
    messageBlock.textContent = 'Генерация переводов...';

    fetch(translationVars.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'generate_translations',
            title,
            description,
            _ajax_nonce: translationVars.nonce
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.data.title_en) document.querySelector('input[name="title_en"]').value = data.data.title_en;
            if (data.data.title_ro) document.querySelector('input[name="title_ro"]').value = data.data.title_ro;
            if (data.data.description_en) document.querySelector('textarea[name="description_en"]').value = data.data.description_en;
            if (data.data.description_ro) document.querySelector('textarea[name="description_ro"]').value = data.data.description_ro;

            messageBlock.style.color = 'green';
            messageBlock.textContent = 'Переводы успешно сгенерированы и заполнены.';
        } else {
            messageBlock.style.color = 'red';
            messageBlock.textContent = 'Ошибка перевода.';
        }
    })
    .catch(() => {
        messageBlock.style.color = 'red';
        messageBlock.textContent = 'Ошибка связи с сервером.';
    });
}
