/**
 *     .addEntry('admin-dates-reservation-data', './assets/admin-dates-reservation-data-manager.js')
 *     templates\admin\dates\edit.html.twig
 */

const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';
Routing.setRoutingData(routes);


$(() => {
    let $wrapper = $('#js-admin-dates-wrapper');
    let AdminDatesReservationDataManager = new adminDatesReservationDataManager($wrapper);
});

class adminDatesReservationDataManager {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on(
            'click',
            '.js-show-reservation-data',
            this.showReservationData.bind(this)
        );
    }

    showReservationData(event) {
        event.preventDefault();
        const self = this;

        const traveller =
            $(event.currentTarget)
            .data('traveller-id');
        (async(traveller) => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('date_show_traveller_data', {
                        'traveller': traveller
                    }),
                    method: 'POST'
                });
                Swal.fire({
                    title: 'Traveller Data',
                    /* text: response, */
                    html: response.html,
                    icon: 'success',
                    confirmButtonText: 'cool'
                });
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })(traveller);
    }
}