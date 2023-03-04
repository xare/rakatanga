const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';

class travellersApp {
    constructor() {
        this.$calculator = $('[data-container="calculator"]');
        this.locale = $('html').attr('lang');
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

    _addTravellersForms(reservation, nbpilotes, nbaccomp) {
        console.info(parseInt(nbpilotes) + parseInt(nbaccomp));
        if (parseInt(nbpilotes) + parseInt(nbaccomp) == 1) {
            $('#js-reservation-payment')
                .removeAttr('title')
                .removeAttr('data-bs-tooltip')
                .removeClass('disabled');
            console.info('disabled, should have been removed');
            document.getElementById('js-reservation-payment').href = Routing.generate('reservation_payment', { '_locale': this.locale, 'reservation': reservation });
            return false;
        }
        console.info('outside of reservation');
        const isInitialized = this.$calculator.data('isInitialized');
        const self = this;
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('add-travellers-forms', {
                            '_locale': self.locale
                        }),
                        method: 'POST',
                        data: {
                            nbpilotes,
                            nbaccomp,
                            reservation
                        }
                    });
                    $('[data-container="js-travellers-form"]').html(response.html);

                } catch (error) {
                    console.error(error);
                }
            }
        )();
    }
    assignUserDataToTravellerForm(event) {
        event.preventDefault();
        const $formContainer = $(event.currentTarget).closest('[data-container="js-traveller-form-container"]');
        $formContainer.find('button').hide();
        $formContainer
            .siblings('.js-contains-button')
            .find('.js-assign-to-user')
            .remove();

        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('ajax-assign-user'),
                    type: "POST"
                });
                $('input[name^="traveller"][name$="[email]"]').each(function() {
                    // Check if the value of the email input field matches the user's email address
                    if ($(this).val() === response.user.email) {
                        // Get the parent container element that contains all the form fields
                        var parentContainer = $(this).closest('[data-container="js-traveller-form-container"]');
                        console.info(event.currentTarget);
                        let buttonElement = $(event.currentTarget.outerHTML);
                        buttonElement.removeAttr('style');
                        parentContainer.find('h5').after(buttonElement[0].outerHTML);
                        // Loop through all the form fields contained within the parent container
                        parentContainer.find('input, select, textarea').each(function() {
                            // Remove the value attribute from each form field
                            //$(this).removeAttr('value');
                            this.value = '';
                        });
                    }

                });
                $formContainer.find('[name*="nom"]').val(response.user.nom);
                $formContainer.find('[name*="prenom"]').val(response.user.prenom);
                $formContainer.find('[name*="email"]').val(response.user.email);
                $formContainer.find('[name*="telephone"]').val(response.user.telephone);

            } catch (jqXHR) {
                console.error(jqXHR);
                //this._notifyErrorToUser(jqXHR);
            }
        })();
    }
};

export default travellersApp;