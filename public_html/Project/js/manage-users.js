document.addEventListener('DOMContentLoaded', () => {

    const abcToggleBtn = document.getElementById('abc-toggle');
    const addUserBtn = document.getElementById('open-create-user-panel-btn');
    const limitSelect = document.getElementById('limit');
    const search = document.getElementById('search-user');
    const optionsForm = document.getElementById('options-form');

    const main = document.querySelector('main');
    const aside = document.querySelector('aside');

    limitSelect.addEventListener('change', () => {
        const pageInput = document.querySelector('[name="page"]');
        if (pageInput) pageInput.value = 1;
        optionsForm.submit();
    });

    abcToggleBtn.addEventListener('click', () => {
        const abcValue = document.getElementById('abc-value');
        abcValue.value = (abcValue.value === 'ASC') ? 'DESC' : 'ASC';
        optionsForm.submit();
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