document.addEventListener('DOMContentLoaded', () => {
    const minimizeButton = document.getElementById('minimize-btn');
    const openAsideButton = document.getElementById('open-aside-btn');

    const main = document.querySelector('main');
    const aside = document.querySelector('aside');

    const incidentCanvas = document.getElementById('homepageIncidentChart');
    const visitCanvas = document.getElementById('homepageVisitChart');


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

    if (incidentCanvas && typeof incidentData !== 'undefined') {
        new Chart(incidentCanvas, {
            type: 'doughnut',
            data: {
                labels: incidentData.map(item => item.severity),
                datasets: [{
                    data: incidentData.map(item => item.count)
                }]
            }
        });
    }
    
    if (visitCanvas && typeof visitData !== 'undefined') {
        new Chart(visitCanvas, {
            type: 'bar',
            data: {
                labels: visitData.map(item => item.browser_name),
                datasets: [{
                    data: visitData.map(item => item.count)
                }]
            }
        });
    }
});