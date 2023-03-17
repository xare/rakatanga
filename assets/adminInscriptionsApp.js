const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';
import { async } from 'regenerator-runtime';

Routing.setRoutingData(routes);

class adminInscriptionsApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;

        this.$wrapper.on(
            'click',
            '[data-action="js-add-user"]',
            this.convert2user.bind(this)
        );
    }

    convert2user(event) {
        event.preventDefault();
        const inscription = $(event.currentTarget).data('inscription');

        (
            async() => {
                try {
                    const response1 = await $.ajax({
                        url: Routing.generate('inscriptions_preadd_user', { inscription }),
                        type: 'POST'
                    });
                    const swalResponse = await Swal.fire({
                        title: 'Convierte a esta inscripción en un usuario',
                        html: response1.message,
                        showCloseButton: true,
                        showCancelButton: true,
                        preConfirm: () => {
                            return {
                                'password': document.getElementById('swal-input-password').value,
                            }
                        }

                    });
                    console.log(swalResponse);
                    if (swalResponse.isConfirmed) {
                        (
                            async() => {
                                try {
                                    const response2 = await $.ajax({
                                        'url': Routing.generate('inscriptions_add_user', { inscription }),
                                        'type': 'POST'
                                    });

                                    Swal.fire({
                                        'html': response2.message
                                    });
                                } catch (jqXHR) {
                                    console.error(jqXHR);

                                }
                            })();
                    } else {
                        console.info(swalResponse);
                    }
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            }
        )();
    }
}

export default adminInscriptionsApp;