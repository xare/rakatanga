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

    handleAddTraveller(event) {
        event.preventDefault();
        const reservationId = this.$calculator.data('reservation');
        const $travellersForm = $('[data-container="js-travellers-form"]');
        const formData = $travellersForm.find('input').serialize();
        const $travellersFormContainers = $travellersForm.find('[data-container="js-travellers-form-container"]');
        if (!this._validateForm()) {
            const InvalidResponse = Swal.fire({
                title: $travellersFormContainers.data('validation-title'),
                text: $travellersFormContainers.data('validation-message'),
            });
            return false;
        }
        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('ajax-add-travellers', {
                        'reservation': reservationId
                    }),
                    data: formData,
                    method: "POST"
                });
                $('[data-container="js-travellers-form"]')
                    .find('.card-body')
                    .html(response.travellersTableHtml);
                $('[data-container="calculator-wrapper"]').attr('data-travellers-added', 'yes');
                if (document.getElementById('js-reservation-payment') === null) {
                    $('[data-container="calculator-wrapper"]').append(response.cardLoggedUser);
                }
                $('#js-reservation-payment')
                    .removeAttr('title')
                    .removeAttr('data-bs-tooltip')
                    .removeAttr('disabled')
                    .removeClass('disabled');
                document.getElementById('js-reservation-payment').href = Routing.generate('reservation_payment', { '_locale': this.locale, 'reservation': reservationId });
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })();
    }
    _validateForm() {
        const form = document.querySelector('form[data-container="js-travellers-form"]');
        const inputTextElements = Array.from(form.elements).filter(element => element.type === "text");
        const inputEmailElements = Array.from(form.elements).filter(element => element.type === "email");
        const isTextEmpty = inputTextElements.some(element => element.value.trim() === "");
        const isEmailEmpty = inputEmailElements.some(element => element.value.trim() === "");
        if (isTextEmpty === true || isEmailEmpty === true) {
            return false;
        } else {
            return inputEmailElements.every(element => this._emailValidate(element.value));
        }
    }
    _emailValidate(emailAddress) {
        const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return emailRegex.test(emailAddress);
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
        if (parseInt(nbpilotes) + parseInt(nbaccomp) == 1) {
            $('#js-reservation-payment')
                .removeAttr('title')
                .removeAttr('data-bs-tooltip')
                .removeClass('disabled');
            document.getElementById('js-reservation-payment').href = Routing.generate('reservation_payment', { '_locale': this.locale, 'reservation': reservation });
        }
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