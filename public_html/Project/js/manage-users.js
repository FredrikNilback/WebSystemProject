document.addEventListener('DOMContentLoaded', () => {

    const abcToggleBtn = document.getElementById('abc-toggle');
    const addUserBtn = document.getElementById('open-create-user-panel-btn');
    const roleForm = document.getElementById('options-form');

    abcToggleBtn.addEventListener('click', () => {
        const abcValue = document.getElementById('abc-value');

        abcValue.value = (abcValue.value === 'ASC') ? 'DESC' : 'ASC';
        roleForm.submit();
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
            roleForm.submit();
        });
    });

});