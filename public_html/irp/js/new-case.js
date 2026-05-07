document.addEventListener('DOMContentLoaded', () => {


    // kollar ifall adressen innehåller success=true
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const incidentId = urlParams.get('id');
        const message = "Your form has been sent. Incident nr: " + incidentId;
        
        // alerten som kommer upp
        alert(message);
        
        // visar ett meddelande
        const successDiv = document.getElementById('success-message');
        if (successDiv) {
            successDiv.style.display = 'block';
            successDiv.innerHTML = message; // lägger in texten
        }
    }
});