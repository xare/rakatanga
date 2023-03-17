import NotificationsApp from './notificationsApp.js';

$(document).ready(function() {
    const notificationsApp = new NotificationsApp($("[data-container='js-reservation-admin-index']"));
    setInterval(function() { notificationsApp.notifyNewReservation() }, 60000);
});