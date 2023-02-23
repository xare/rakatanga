const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';

import travellersApp from './travellersApp.js';
import Swal from 'sweetalert2';

Routing.setRoutingData(routes);

$(document).ready(function() {
    const $wrapper = $('[data-container="js-admin-reservation-wrapper"]');
    let adminReservationApp = new AdminReservationApp($wrapper);
});

class AdminReservationApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.travellersApp = new travellersApp();
        this.$wrapper.on(
            'click',
            '[data-action="js-open-traveller-form"]',
            this.travellersApp.openTravellersSwalForm.bind(this.travellersApp));
    }
}