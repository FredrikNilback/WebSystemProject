document.addEventListener('DOMContentLoaded', () => {
    const minimizeButton = document.getElementById('minimize-btn');
    const openAsideButton = document.getElementById('open-aside-btn');

    const main = document.querySelector('main');
    const aside = document.querySelector('aside');


    minimizeButton.addEventListener('click', () => {
        main.classList.add('fullscreen');
        aside.classList.add('hidden');
        openAsideButton.classList.remove('hidden');
    })

    openAsideButton.addEventListener('click', () => {
        main.classList.remove('fullscreen');
        aside.classList.remove('hidden');
        openAsideButton.classList.add('hidden');
    })
});