document.addEventListener("DOMContentLoaded", function () {

const historyLabels = incidentHistoryData.map(item => item.date);
const historyCounts = incidentHistoryData.map(item => item.count);
const categoryLabels = topIncidentCategoriesData.map(item => item.category);
const categoryCounts = topIncidentCategoriesData.map(item => item.count);
const severityLabels = incidentSeverityChartData.map(item => item.severity);
const severityCounts = incidentSeverityChartData.map(item => item.count);
const resolutionLabels = resolutionTimeChartData.map(item => 'Week' + item.week);
const resolutionCounts = resolutionTimeChartData.map(item => item.avg_time);


new Chart(document.getElementById('History_lineChart'), {       //linecharten
    type: 'line', 
    data: {
        labels: historyLabels,
        datasets: [{
            label: 'Number of incidents',
            data: historyCounts
        }]
    }
});

new Chart(document.getElementById('Top_Incidents_pieChart'), {       //piecharten
    type: 'pie', 
    data: {
        labels: categoryLabels,
        datasets: [{
            label: 'Top Incidents',
            data: categoryCounts
        }]
    }
});

new Chart(document.getElementById('Incident_Overview_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: severityLabels,
        datasets: [{
            label: 'Number of incidents per severity',
            data: severityCounts
        }]
    }
});

new Chart(document.getElementById('Resolution_Time_barChart'), {       //barcharten
    type: 'bar', 
    data: {
        labels: resolutionLabels,
        datasets: [{
            label: 'Avarage Resolution Time',
            data: resolutionCounts
        }]
    }
});

});