document.addEventListener("DOMContentLoaded", function () {

new Chart(document.getElementById('History_lineChart'), {       //linecharten
    type: 'line', 
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
        datasets: [{
            label: 'Number of incidents',
            data: [45, 35, 26, 22, 15, 35, 46, 32, 11, 17, 29, 48]
        }]
    }
});

new Chart(document.getElementById('Top_Incidents_pieChart'), {       //piecharten
    type: 'pie', 
    data: {
        labels: ['Phishing Attacks', 'Malware', 'Ransomware', 'Unautharized Access'],
        datasets: [{
            label: 'Top Incidents',
            data: [5, 10, 3, 6]
        }]
    }
});

new Chart(document.getElementById('Incident_Overview_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: ['Low', 'Medium', 'High', 'Critical'],
        datasets: [{
            label: 'Number of incidents per severity',
            data: [17, 27, 6, 4]
        }]
    }
});

new Chart(document.getElementById('Resolution_Time_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: ['Week 9', 'Week 10', 'Week 11', 'Week 12', 'Week 13', 'Week 14', 'Week 15'],
        datasets: [{
            label: 'Avarage Resolution Time',
            data: [3, 5, 2, 14, 17, 4, 8]
        }]
    }
});

});