const addUserBtn = document.getElementById('open-create-user-panel-btn');

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

        asideClass.remove('shown')
        asideClass.add('hidden');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.role-select').forEach(select => {
        const button = select.form.querySelector('.update-role-btn');
        const original = select.value;

        button.disabled = true;

        select.addEventListener('change', () => {
            button.disabled = (select.value === original);
        });    
    });
});
