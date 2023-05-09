const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';

Routing.setRoutingData(routes);

document.addEventListener('load', loadPopup());


function loadPopup() {
    (
        async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('ajax-show-popup'),
                    type: 'GET'
                });
                if (null === response.html) return;

                Swal.fire({
                    html: response.html,
                    timer: 15000,
                    timerProgressBar: true,
                });
            } catch (error) {
                console.error('error', error);
            }
        }
    )();
}