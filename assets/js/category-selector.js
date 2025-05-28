const ajaxUrl = categorySelectorVars.ajaxUrl;

document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('category-selectors');
    const preselectedContainer = wrapper.querySelector('#preselected-categories');
    const preselectedTerms = preselectedContainer && preselectedContainer.dataset.terms
        ? JSON.parse(preselectedContainer.dataset.terms)
        : [];

    function createSelect(level, options, selectedId = null) {
        const container = document.createElement('div');
        container.classList.add('category-select-wrapper');
        container.setAttribute('data-level', level);

        const label = document.createElement('label');
        label.classList.add('label-medium');

        switch (level) {
            case 0:
                label.textContent = translations.labelLevel0;
                break;
            case 1:
                label.textContent = translations.labelLevel1;
                break;
            default:
                label.textContent = translations.labelLevel2;
                break;
        }

        const select = document.createElement('select');
        select.name = 'product_categories[]';
        select.classList.add('category-select');
        select.classList.add('select-tertiary');
        select.classList.add('body-small-regular');
        select.setAttribute('data-level', level);

        const defaultOption = document.createElement('option');
        defaultOption.textContent = translations.selectCategory;
        defaultOption.value = '';
        select.appendChild(defaultOption);

        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.term_id;
            option.textContent = opt.name[categorySelectorVars.language] || opt.name['ru'];
            if (selectedId && parseInt(selectedId) === parseInt(opt.term_id)) {
                option.selected = true;
            }
            select.appendChild(option);
        });


        container.appendChild(label);
        container.appendChild(select);
        return container;
    }

    function removeLowerLevels(startLevel) {
        const wrappers = wrapper.querySelectorAll('.category-select-wrapper');
        wrappers.forEach(wrap => {
            const level = parseInt(wrap.getAttribute('data-level'));
            if (level > startLevel) {
                wrap.remove();
            }
        });
    }

    function loadSubcategories(parentId = 0, level = 0, selectedId = null) {
        fetch(`${ajaxUrl}?action=get_subcategories&parent=${parentId}`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) return;

                const selectWrapper = createSelect(level, data, selectedId);
                const select = selectWrapper.querySelector('select');
                wrapper.appendChild(selectWrapper);

                select.addEventListener('change', function () {
                    const currentLevel = parseInt(this.dataset.level);
                    removeLowerLevels(currentLevel);
                    if (this.value) {
                        loadSubcategories(this.value, currentLevel + 1);
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

            const selectWrapper = createSelect(level, data, selectedId);
            const select = selectWrapper.querySelector('select');
            wrapper.appendChild(selectWrapper);

            select.addEventListener('change', function () {
                const currentLevel = parseInt(this.dataset.level);
                removeLowerLevels(currentLevel);
                if (this.value) {
                    loadSubcategories(this.value, currentLevel + 1);
                }
            });

            currentParent = selectedId;
            level++;
        }

        const last = await fetch(`${ajaxUrl}?action=get_subcategories&parent=${currentParent}`)
            .then(res => res.json());

        if (last.length > 0) {
            const selectWrapper = createSelect(level, last);
            const select = selectWrapper.querySelector('select');
            wrapper.appendChild(selectWrapper);

            select.addEventListener('change', function () {
                const currentLevel = parseInt(this.dataset.level);
                removeLowerLevels(currentLevel);
                if (this.value) {
                    loadSubcategories(this.value, currentLevel + 1);
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
