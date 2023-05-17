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
                        'reservation': reservationId,
                        '_locale': this.locale
                    }),
                    data: formData,
                    method: "POST",
                    beforeSend: () => {
                        $('.card-logged-user')
                            .find('.card-body')
                            .empty();
                        let waitingText = $('[data-container="calculator"]').data('waiting');
                        $('.card-logged-user')
                            .find('.card-body')
                            .append(`<div><i class="fas fa-spinner fa-spin"></i> ${waitingText}`)
                            .hide();
                    }
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
        console.info(form.elements);
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

        let sourceElement;
        let targetElement;

        // Get the form elements
        let formElements = document.querySelectorAll('[data-container="js-travellers-form-container"]');

        // Loop through the form elements
        formElements.forEach((formElement) => {
            console.info(formElement);
            console.info(formElement.getAttribute('data-index'), formElement.getAttribute('data-position'));
            if (formElement.getAttribute('data-position') === "pilote" 
                && formElement.querySelector('[data-action="js-assign-to-user"]')) {
                    console.info('1st condition');
                    sourceElement = formElement;
                    targetElement = this._getFirstFormElement(formElements, "passager");
                    console.info(sourceElement);
            } else if (formElement.getAttribute('data-position') === "passager" 
                && formElement.querySelector('[data-action="js-assign-to-user"]')) {
                    console.info('2nd condition');
                sourceElement = formElement;
                targetElement = this._getFirstFormElement(formElements, "pilote");
                return;
            }     
        });

        // Check if the button is already in the target element
        let buttonInTarget = targetElement.querySelector('[data-action="js-assign-to-user"]');
        if (buttonInTarget) {
            [sourceElement, targetElement] = [targetElement, sourceElement];
        }
        // Get the button from the sourceElement and add click event
        let button = event.currentTarget;
        console.info(sourceElement);
        console.info(targetElement);
        if (sourceElement.getAttribute('data-position') === "pilote") {
            this._swapValues(sourceElement, targetElement);
        } else {
            this._swapValues(sourceElement, this._getFirstPilote(formElements));
        }
    }

    // Function to swap values between two form elements
    _swapValues(source, target) {
        let sourceInputs = Array.from(source.querySelectorAll('input')).filter(input => input.name.indexOf("position") === -1);
        console.info(sourceInputs);
        let targetInputs = Array.from(target.querySelectorAll('input')).filter(input => input.name.indexOf("position") === -1);
        console.info(targetInputs);

        let sourceButton = source.querySelector('button');
        let targetButton = target.querySelector('button');

        // Swap the input fields, excluding the position fields
        for (let i = 0; i < sourceInputs.length; i++) {
            let temp = sourceInputs[i].value;
            sourceInputs[i].value = targetInputs[i].value;
            targetInputs[i].value = temp;
        }
        console.info(source);
        console.info(sourceButton);
        console.info(target);
        console.info(targetButton);
        // Swap the button
        if (sourceButton) {
            source.removeChild(sourceButton);
            target.appendChild(sourceButton);
        } else if (targetButton) {
            target.removeChild(targetButton);
            source.appendChild(targetButton);
        }
    }
    // Function to get the first pilote element
    _getFirstFormElement(formElements,positon) {
    for (let i = 0; i < formElements.length; i++) {
        if (formElements[i].getAttribute('data-position') === "pilote") {
            return formElements[i];
        }
    }
    return null;
}
}
export default travellersApp;