import NotificationsApp from './notificationsApp.js';

$(document).ready(function() {
    /* const notificationsApp = new NotificationsApp($("[data-container='js-reservation-admin-index']")); */
    const notificationsApp = new NotificationsApp($("[data-container='js-reservation-admin-index']"));
    console.info("Notifications");
    setInterval(function() { notificationsApp.notifyNewReservation() }, 60000);
});