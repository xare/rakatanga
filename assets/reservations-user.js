import reservationsApp from './reservationsApp.js';

$(() => {

    const $wrapper = $('#js-user-reservations_wrapper');
    var ReservationsApp = new reservationsApp($wrapper);
});