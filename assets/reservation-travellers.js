import reservationApp from './reservationApp.js';

$(document).ready(function() {

    let $wrapper = $('#js-reservation-wrapper');

    var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'));
    triggerTabList.forEach(function(triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);

        triggerEl.addEventListener('click', function(event) {
            event.preventDefault();
            tabTrigger.show();
        })
    })

    var ReservationApp = new reservationApp($wrapper);

});