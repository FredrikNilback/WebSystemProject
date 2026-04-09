document.addEventListener("DOMContentLoaded", function () {

new Chart(document.getElementById('Visits_History_lineChart'), {       //linecharten
    type: 'line', 
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
        datasets: [{
            label: 'Visits over time',
            data: [34, 67, 230, 134, 128, 56, 46, 32, 56, 78, 114, 137]
        }]
    }
});


new Chart(document.getElementById('Visits_Weekly_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: ['Week 9', 'Week 10', 'Week 11', 'Week 12', 'Week 13', 'Week 14', 'Week 15', 'Week 16', 'Week 17'],
        datasets: [{
            label: 'Visits Weekly',
            data: [48, 34, 23, 25, 78, 45, 36, 23, 25]
        }]
    }
});

new Chart(document.getElementById('Visits_Daily_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Visits Daily',
            data: [3, 5, 2, 14, 17, 4, 8]
        }]
    }
});

new Chart(document.getElementById('Browser_Daily_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: ['Firefox', 'Chrome', 'Safari', 'Microsoft Edge', 'Opera'],
        datasets: [{
            label: 'Visits Browser',
            data: [28, 32, 12, 45, 17]
        }]
    }
});

});