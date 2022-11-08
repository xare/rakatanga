import reservationApp from './reservationApp.js';

$(document).ready(function() {
    let ReservationApp = new reservationApp($('[data-container="reservation"]'));

    var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'));
    triggerTabList.forEach(function(triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);

        triggerEl.addEventListener('click', function(event) {
            event.preventDefault();
            tabTrigger.show();
        })
    })



});