let activeFilters = [];

function filterTable(status) {
    const cards = document.querySelectorAll('.status-card');
    const rows = document.querySelectorAll('.incident-row');

    if (status === 'all') {
        activeFilters = [];
        cards.forEach(c => c.classList.remove('active'));
    } else {
        // hittar boxen och ändrar färg på den 
        // letar efter den box som har exakt det status-ordet i sin onclick grej
        cards.forEach(card => {
            if (card.getAttribute('onclick').includes("'" + status + "'")) {
                card.classList.toggle('active');
            }
        });

        // listan uppdateras med valda filter
        if (activeFilters.includes(status)) {
            activeFilters = activeFilters.filter(s => s !== status);
        } else {
            activeFilters.push(status);
        }
    }
    // visa/dölj rader
    rows.forEach(row => {
        if (activeFilters.length === 0) {
            row.style.display = '';
        } else {
            // om raden har klassen t.ex. pending eller in progress
            const matches = activeFilters.some(s =>
                row.classList.contains(s.replace(' ', '-'))
            );
            row.style.display = matches ? '' : 'none';
        }
    });
}
