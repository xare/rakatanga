import reservationsApp from './reservationsApp.js';

$(document).ready(function() {

    const $wrapper = $('#js-user-reservations_wrapper');
    var ReservationsApp = new reservationsApp($wrapper);
    ReservationsApp.popUpForMoreData();
});