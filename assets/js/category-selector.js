
const ajaxUrl = categorySelectorVars.ajaxUrl;

document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('category-selectors');
    const preselectedContainer = wrapper.querySelector('#preselected-categories');
    const preselectedTerms = preselectedContainer && preselectedContainer.dataset.terms
        ? JSON.parse(preselectedContainer.dataset.terms)
        : [];

    function createSelect(level, options, selectedId = null) {
        const select = document.createElement('select');
        select.name = 'product_categories[]';
        select.classList.add('category-select');
        select.setAttribute('data-level', level);

        const defaultOption = document.createElement('option');
        defaultOption.textContent = 'Выберите категорию';
        defaultOption.value = '';
        select.appendChild(defaultOption);

        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.term_id;
            option.textContent = opt.name;
            if (selectedId && parseInt(selectedId) === parseInt(opt.term_id)) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        return select;
    }

    function loadSubcategories(parentId = 0, level = 0, selectedId = null) {
        fetch(`${ajaxUrl}?action=get_subcategories&parent=${parentId}`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) return;

                const select = createSelect(level, data, selectedId);
                wrapper.appendChild(select);

                select.addEventListener('change', function () {
                    const next = [...wrapper.querySelectorAll('select')].filter(s => parseInt(s.dataset.level) > level);
                    next.forEach(s => s.remove());

                    if (this.value) {
                        loadSubcategories(this.value, level + 1);
                    }
                });
            });
    }

    async function restoreChain() {
        let currentParent = 0;
        let level = 0;

        for (let i = 0; i < preselectedTerms.length; i++) {
            const selectedId = preselectedTerms[i];

            const data = await fetch(`${ajaxUrl}?action=get_subcategories&parent=${currentParent}`)
                .then(res => res.json());

            if (!data.some(cat => parseInt(cat.term_id) === parseInt(selectedId))) {
                break;
            }

            const select = createSelect(level, data, selectedId);
            wrapper.appendChild(select);

            select.addEventListener('change', function () {
                const next = [...wrapper.querySelectorAll('select')].filter(s => parseInt(s.dataset.level) > level);
                next.forEach(s => s.remove());

                if (this.value) {
                    loadSubcategories(this.value, level + 1);
                }
            });

            currentParent = selectedId;
            level++;
        }

        const last = await fetch(`${ajaxUrl}?action=get_subcategories&parent=${currentParent}`)
            .then(res => res.json());

        if (last.length > 0) {
            const select = createSelect(level, last);
            wrapper.appendChild(select);

            select.addEventListener('change', function () {
                const next = [...wrapper.querySelectorAll('select')].filter(s => parseInt(s.dataset.level) > level);
                next.forEach(s => s.remove());

                if (this.value) {
                    loadSubcategories(this.value, level + 1);
                }
            });
        }
    }

    if (preselectedTerms.length > 0) {
        restoreChain();
    } else {
        loadSubcategories(0, 0);
    }
});
