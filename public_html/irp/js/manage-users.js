document.addEventListener('DOMContentLoaded', () => {

    const sortButtons = document.querySelectorAll('.sort-btn');
    const addUserBtn = document.getElementById('open-create-user-panel-btn');
    const limitSelect = document.getElementById('limit');
    const search = document.getElementById('search-user');
    const optionsForm = document.getElementById('options-form');

    const main = document.querySelector('main');
    const aside = document.querySelector('aside');

    const params = new URLSearchParams(window.location.search);

    if (!params.has('limit')) {
        if (window.innerWidth <= 1920) {
            params.set('limit', '9');
        } else {
            params.set('limit', '16');
        }

        window.location.search = params.toString();
    }

    limitSelect.addEventListener('change', () => {
        const pageInput = document.querySelector('[name="page"]');
        if (pageInput) pageInput.value = 1;
        optionsForm.submit();
    });

    sortButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const newOrder = btn.dataset.order;

            const orderValue = document.getElementById('order-value');
            const directionValue = document.getElementById('direction-value');

            if (orderValue.value === newOrder) {
                directionValue.value = (directionValue.value === 'ASC') ? 'DESC' : 'ASC';
            } else {
                orderValue.value = newOrder;
                directionValue.value = 'ASC';
            }

            const pageInput = document.querySelector('[name="page"]');
            if (pageInput) pageInput.value = 1;

            optionsForm.submit();
        });
    });

    let searchTimeout = null;

    search.addEventListener('input', () => {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {

            const pageInput = document.querySelector('[name="page"]');
            if (pageInput) pageInput.value = 1;

            sessionStorage.setItem('searchSelection', JSON.stringify({
                start: search.selectionStart,
                end: search.selectionEnd,
                value: search.value
            }));

            optionsForm.submit();

        }, 1000);
    });

    addUserBtn.addEventListener('click', () => {

        const isOpen = !aside.classList.contains('hidden');
        sessionStorage.setItem('createUserOpen', isOpen ? '0' : '1');

        if (main.classList.contains('fullscreen')) {
            main.classList.remove('fullscreen');
            main.classList.add('resized');

            aside.classList.remove('hidden');
            aside.classList.add('shown');
        } else {
            main.classList.remove('resized');
            main.classList.add('fullscreen');

            aside.classList.remove('shown');
            aside.classList.add('hidden');
        }
    });

    document.querySelectorAll('.role-select').forEach(select => {
        const button = select.form.querySelector('.update-role-btn');
        const original = select.value;

        button.disabled = true;

        select.addEventListener('change', () => {
            button.disabled = (select.value === original);
        });
    });

    document.querySelectorAll('#role-filtering input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
            optionsForm.submit();
        });
    });

    const savedSearch = sessionStorage.getItem('searchSelection');

    if (savedSearch) {
        const data = JSON.parse(savedSearch);

        search.focus();
        search.setSelectionRange(data.start, data.end);

        sessionStorage.removeItem('searchSelection');
    }

    const panelState = sessionStorage.getItem('createUserOpen');

    if (panelState === '1') {
        main.classList.remove('fullscreen');
        main.classList.add('resized');

        aside.classList.remove('hidden');
        aside.classList.add('shown');
    }
});