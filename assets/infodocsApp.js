const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';

Routing.setRoutingData(routes);

class infodocsApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        $(this.$wrapper).on(
            'click',
            '.js-remove-infodoc',
            this.removeInfodoc.bind(this)
        )
    }

    removeInfodoc(event) {
        event.preventDefault();
        const travel = $(event.currentTarget).data('travel');
        const infodoc = $(event.currentTarget).data('infodoc');
        (
            () => {
                Swal.fire(
                    'Vas a borrar este documento'
                ).then(async(result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await $.ajax({
                                'url': Routing.generate('ajax-remove-infodocs', {
                                    travel,
                                    infodoc
                                }),
                                'type': 'GET'
                            });
                            $('.js-list-infodocs').empty();
                            $('.js-list-infodocs').html(response);
                        } catch (jqXHR) {
                            console.error(jqXHR);
                        }
                    }
                })
            }
        )();
    }
}

export default infodocsApp;