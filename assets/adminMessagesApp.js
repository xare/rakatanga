const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';

Routing.setRoutingData(routes);

class AdminMessagesApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on(
            'click',
            '[data-action="send-checkin-request"]',
            this.handleSendCheckInRequest.bind(this));
    }

    handleSendCheckInRequest(event) {
        event.preventDefault();
        const reservationId = $(event.currentTarget).data('reservationId');
        const self = this;

        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('ajax-reservation-send-checkin-message', {
                        'reservation': reservationId
                    }),
                    beforeSend: self._waitForAjax(),
                    type: "POST"
                });
                $(event.currentTarget).attr('disabled', true);
                console.info(response);
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })();
    }
    _waitForAjax() {
        Swal.fire({
            title: $('[data-action="send-checkin-request"]').data('swal-waiting-title'),
            icon: "info",
            showConfirmButton: true,
            text: $('[data-action="send-checkin-request"]').data('swal-waiting-text')
        });
    }
}

export default AdminMessagesApp;