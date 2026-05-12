document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector("form");
    const incidentInput = document.querySelector("input[name='incident_time']");

    function showError(message) {
        let errorDiv = document.getElementById("error-message");

        if (!errorDiv) {
            errorDiv = document.createElement("div");
            errorDiv.id = "error-message";
            errorDiv.style.background = "#ffdddd";
            errorDiv.style.color = "#a10000";
            errorDiv.style.padding = "10px";
            errorDiv.style.margin = "10px 0";
            errorDiv.style.border = "1px solid #a10000";
            form.prepend(errorDiv);
        }

        errorDiv.textContent = message;

        setTimeout(() => {
            errorDiv.remove();
        }, 4000);
    }

    form.addEventListener("submit", (e) => {
        const selected = new Date(incidentInput.value);
        const now = new Date();

        if (incidentInput.value && selected > now) {
            e.preventDefault();
            showError("Incident time cannot be in the future");
        }
    });

    incidentInput.addEventListener("change", () => {
        const selected = new Date(incidentInput.value);
        const now = new Date();

        if (incidentInput.value && selected > now) {
            showError("Incident time cannot be in the future");
            incidentInput.value = "";
        }
    });


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
