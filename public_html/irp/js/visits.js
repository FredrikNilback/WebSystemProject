document.addEventListener("DOMContentLoaded", function () {

const labels = visitsData.map(v => v.date);
const data = visitsData.map(v => v.count);
console.log(labels);
console.log(data);


new Chart(document.getElementById('Visits_History_lineChart'), {       //linecharten
    type: 'line', 
    data: {
        labels: labels,
        datasets: [{
            label: 'Page Visits over time',
            data: data,
        }]
    }
});


const weeklyLabels = weeklyData.map(w => "Week " + w.week.toString().slice(-2));
const weeklyCounts = weeklyData.map(w => w.count);

new Chart(document.getElementById('Visits_Weekly_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: weeklyLabels,
        datasets: [{
            label: 'Page Visits Weekly',
            data: weeklyCounts
        }]
    }
});


const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const dayLabels = dayData.map(d => dayNames[d.day - 1]);
const dayCounts = dayData.map(d => d.count);

new Chart(document.getElementById('Visits_Daily_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: dayLabels,
        datasets: [{
            label: 'Page Visits Daily',
            data: dayCounts
        }]
    }
});


const browserLabels = browserData.map(b => b.browser_name);
const browserCounts = browserData.map(b => b.count);

new Chart(document.getElementById('Browser_Daily_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: browserLabels,
        datasets: [{
            label: 'Page Visits per Browser',
            data: browserCounts
        }]
    }
});

});