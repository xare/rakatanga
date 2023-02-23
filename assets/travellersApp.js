const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';

class travellersApp {
    constructor() {

    }

    openTravellersSwalForm(event) {
        event.preventDefault();
        const self = this;
        const traveller = $(event.currentTarget).data('traveller');
        (
            async() => {
                try {
                    const travellerFormResponse = await $.ajax({
                        url: Routing.generate('ajax-edit-traveller', {
                            traveller
                        }),
                        type: "GET"
                    });
                    console.info(travellerFormResponse);

                    (
                        async(travellerFormResponse) => {
                            const { value: formValues } = await Swal.fire({
                                title: travellerFormResponse.title,
                                html: travellerFormResponse.swalHtml,
                                focusConfirm: false,
                                preConfirm: () => {
                                    return {
                                        prenom: $('#traveller_prenom').val(),
                                        nom: $('#traveller_nom').val(),
                                        email: $('#traveller_email').val(),
                                        telephone: $('#traveller_telephone').val(),
                                        traveller
                                    }
                                }
                            });
                            if (formValues) {
                                const row = $(event.currentTarget).closest('tr');
                                const response = self._handleSubmitTraveller(
                                    formValues, row);
                            }
                        }
                    )(travellerFormResponse);
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            }
        )();
    }

    _handleSubmitTraveller(formData, row) {
        const traveller = formData.traveller;
        (
            async() => {
                try {
                    const response = await $.ajax({
                        type: 'POST',
                        url: Routing.generate('ajax-save-traveller', { traveller }),
                        data: formData,
                        datatype: "json",
                        encode: true
                    });
                    row.html(response.html);
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            }
        )();
    }
};

export default travellersApp;