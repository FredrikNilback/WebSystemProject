document.addEventListener('DOMContentLoaded', () => {

    const link = document.getElementById('forgot-pwd');
    const audio = document.getElementById('forgot-pwd-audio');

    if (link && audio) {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            audio.currentTime = 0;
            audio.play();
        });
    }

    const timeOfDayImage = document.getElementById('time-of-day-img');
    const greeting = document.getElementById('greeting');
    const currentTime = (new Date()).getHours();

    switch (true) {
        case currentTime < 6:
            greeting.innerHTML = 'Good Night!';
            timeOfDayImage.src = 'images/login/time_of_day/night.png';
            break;
        case currentTime < 12:
            greeting.innerHTML = 'Good Morning!';
            timeOfDayImage.src = 'images/login/time_of_day/morning.png';
            break;
        case currentTime < 18:
            greeting.innerHTML = 'Good Afternoon!';
            timeOfDayImage.src = 'images/login/time_of_day/afternoon.png';
            break;
        case currentTime < 21:
            greeting.innerHTML = 'Good Evening!';
            timeOfDayImage.src = 'images/login/time_of_day/evening.png';
            break;
        default:
            greeting.innerHTML = 'Good Night!';
            timeOfDayImage.src = 'images/login/time_of_day/night.png';
            break;
    }

    const main = document.querySelector('main');
    const hero = document.getElementById('login-hero');
    main.addEventListener("mousemove", (e) => {
        const rect = main.getBoundingClientRect();

        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;

        main.style.setProperty("--x", `${x}%`);
        main.style.setProperty("--y", `${y}%`);
        hero.style.setProperty("--x", `${x}%`);
        hero.style.setProperty("--y", `${y}%`);
    });

});
