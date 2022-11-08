const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';

Routing.setRoutingData(routes);

import Swal from 'sweetalert2';

class AdminMessagesApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on(
            'click',
            '.js-send-checkin-request',
            this.handleSendCheckInRequest.bind(this));
    }

    handleSendCheckInRequest(event) {
        event.preventDefault();
        const reservationId = $(event.currentTarget).data('reservationId');
        console.info('send check in request');
        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('ajax-reservation-send-checkin-message', {
                        'reservation': reservationId
                    }),
                    type: "POST"
                });
                $(event.currentTarget).attr('disabled', true);
                console.info(response);
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })();
    }
}

export default AdminMessagesApp;