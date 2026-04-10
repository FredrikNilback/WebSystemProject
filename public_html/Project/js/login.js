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
        timeOfDayImage.src = 'images/time_of_day/night.png';
        break;
    case currentTime < 12:
        greeting.innerHTML = 'Good Morning!';
        timeOfDayImage.src = 'images/time_of_day/morning.png';
        break;
    case currentTime < 18:
        greeting.innerHTML = 'Good Afternoon!';
        timeOfDayImage.src = 'images/time_of_day/afternoon.png';
        break;
    case currentTime < 21:
        greeting.innerHTML = 'Good Evening!';
        timeOfDayImage.src = 'images/time_of_day/evening.png';
        break;
    default:
        greeting.innerHTML = 'Good Night!';
        timeOfDayImage.src = 'images/time_of_day/night.png';
        break;
}
