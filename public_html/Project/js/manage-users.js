document.addEventListener('DOMContentLoaded', () => {

    const abcToggleBtn = document.getElementById('abc-toggle');
    const addUserBtn = document.getElementById('open-create-user-panel-btn');
    const limitSelect = document.getElementById('limit');
    const optionsForm = document.getElementById('options-form');

    limitSelect.addEventListener('change', () => {
        optionsForm.submit();
    });

    abcToggleBtn.addEventListener('click', () => {
        const abcValue = document.getElementById('abc-value');

        abcValue.value = (abcValue.value === 'ASC') ? 'DESC' : 'ASC';
        optionsForm.submit();
    });

    addUserBtn.addEventListener('click', () => {
        const mainClass = document.querySelector('main').classList;
        const asideClass = document.querySelector('aside').classList;

        if (mainClass[0] === 'fullscreen') {
            mainClass.remove('fullscreen');
            mainClass.add('resized');

            asideClass.remove('hidden');
            asideClass.add('shown');
        } else {
            mainClass.remove('resized');
            mainClass.add('fullscreen');

            asideClass.remove('shown');
            asideClass.add('hidden');
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
});