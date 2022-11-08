const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
//import reservationCalculator from './reservation-table-calculator.js';
import Swal from 'sweetalert2';
import { data } from 'jquery';

Routing.setRoutingData(routes);

class reservationApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$calculator = $('[data-container="calculator"]');
        this.$calculatorWrapper = $('[data-container="calculator-wrapper"]');
        this.wrapperData = this.$wrapper.data();
        this.locale = $('html').attr('lang');
        this.$wrapper.on(
            'change',
            '[data-action="handle-change-nb-pilotes"]',
            this.handleChangeNbPilotes.bind(this)
        );
        this.$wrapper.on(
            'change',
            '[data-action="handle-change-nb-accomp"]',
            this.handleChangeNbPassagers.bind(this)
        );

        this.$wrapper.on(
            'change',
            '.js-options-row-select input[type="range"]',
            this.handleAddOption.bind(this)
        )

        this.$wrapper.on(
            'submit',
            '.js-register-form',
            this.initializeReservationRegister.bind(this)
        );

        this.$wrapper.on(
            'click',
            '[data-action="js-initialize-reservation-logged"]',
            this.initializeReservationLogged.bind(this)
        );

        /*  this.$wrapper.on(
             'click',
             '#js-create-invoice',
             this.openInvoiceForm.bind(this)
         ) */

        this.$wrapper.on(
            'click',
            '#js-add-travellers',
            this.handleAddTraveller.bind(this)
        )

        this.$wrapper.on(
            'click',
            '.js-assign-to-user',
            this.assignUserDataToTravellerForm.bind(this)
        );

        this.$wrapper.on(
            'click',
            '#js_reservation_status_initialized',
            this.setReservationStatus.bind(this)
        )

        this.$wrapper.on(
            'click',
            '.js-apply-codepromo',
            this.applyCodePromo.bind(this)
        )

        this.$wrapper.on(
            'click',
            '[data-action="updateReservation"]',
            this.updateReservation.bind(this)
        )

        /*this.ReservationCalculator.$calculator.on(
            'click',
            '#js-update-changes',
            this.updateChanges.bind(this)
        )*/

    }

    handleChangeNbPilotes(e) {
        e.preventDefault();
        const type = "pilotes";
        const nbPilotes = $(e.currentTarget).val();
        const reservation = this.$wrapper.data('reservation-id');
        this._updateNbFromSelect(type, nbPilotes);
        const nbAccomp = this.$calculator.data('nbaccomp');
        $('[data-action="handle-change-nb-accomp"]').attr('max', nbPilotes);
        this._changeOptionsAmmount(
            nbPilotes,
            nbAccomp
        );

        //$('[data-container="js-card-user"]').removeClass('d-none').fadeIn(1000);
        $('[data-action="js-initialize-reservation-logged"]').show();
        this._changeNb(
            reservation,
            nbPilotes,
            type
        );
    }


    handleChangeNbPassagers(event) {
        event.preventDefault();
        var type = "accomp";
        var nbaccomp = $(event.currentTarget).val();
        var reservation = this.$wrapper.data('reservation');
        this._updateNbFromSelect(type, nbaccomp);

        let nbpilotes = this.$calculator.data('nbpilotes');

        this._changeOptionsAmmount(
            nbpilotes,
            nbaccomp
        );
        //$('[data-container="js-card-user"]').fadeIn(1000);
        $('[data-action="js-initialize-reservation-logged"]').fadeIn(1000);
        this._changeNb(
            reservation,
            nbaccomp,
            type
        );

    }
    _updateNbFromSelect(type, nb) {
        this.$calculator.attr('data-nb' + type, nb);
    }

    _changeNb(reservation, nb, type) {
        this.$calculator.data(`nb${type}`, nb);
        console.info(this.$calculator);
        let data = this.$calculator.data();
        data.locale = $('html').attr('lang');
        console.info('data', data);
        $('[data-container="nb' + type + '"]').empty().html(nb);

        const self = this;
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('update-calculator', { '_locale': self.locale }),
                        type: 'POST',
                        beforeSend: () => {
                            $('[data-container="calculator"]').find('.card-body > div > table').hide();
                            let waitingText = $('[data-container="calculator"]').data('waiting');
                            $('[data-container="calculator"]').find('.card-body').append(`<div><i class="fas fa-spinner fa-spin"></i> ${waitingText}`);
                        },
                        /* beforeSend: () => {
                            console.info('before send');
                            $('[data-container="calculator"]')
                                .append('<div class="cover-wait">Hello</div>')
                        }, */
                        data
                    });
                    $('[data-container="calculator-wrapper"]').empty().append(response);
                    //$('.card-logged-user').removeClass('d-none');
                    if (data.nbpilotes != 0)
                        self.$calculator.data('nbpilotes', data.nbpilotes);
                    if (data.nbaccomp != 0)
                        self.$calculator.data('nbaccomp', data.nbaccomp);
                    self._showUpdateButton();
                    self._showInitializeButton();
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            }
        )();
    }

    handleAddOption(event) {
        event.preventDefault();
        const self = this;
        const previousData = this.$calculator.data() || "";

        const optionInputName = $(event.currentTarget).attr('name'); //returns reservation_user[option][00]
        const ammount = parseInt($(event.currentTarget).val());
        const matches = optionInputName.match(/(\d+)/);
        const optionTitle = $(event.currentTarget).closest('tr').data('optionTitle');
        const optionPrice = $(event.currentTarget).closest('tr').data('optionPrice');
        const reservation = this.$wrapper.data('reservation');

        let previousOptions = previousData.options == '' ? [] : previousData.options;
        let finalOptions = [];

        this._renderRangeOutput($(event.currentTarget), $(event.currentTarget).attr('max'));
        let date = { dateId: previousData.dateId };
        let currentOption = {
            id: matches[0],
            ammount,
            price: optionPrice,
            title: optionTitle
        };


        if (previousOptions.every((previousOption) => { //returns true when then new option selected was not part of the previousOptions
                return (previousOption.id !== null && previousOption.id !== currentOption.id)
            })) {
            finalOptions = [
                ...previousOptions,
                ...[currentOption]
            ];
        } else { // when the option was already selected but when we change the ammount for that option.
            previousOptions.forEach((previousOption) => {
                if (previousOption.id == currentOption.id)
                    return (previousOption.ammount = parseInt(currentOption.ammount));
            });
            finalOptions = previousOptions;
        }
        let requestData = {
            ...date,
            'options': finalOptions,
            reservation,
            isInitialized: previousData.isInitialized,
            'nbpilotes': previousData.nbpilotes,
            'nbaccomp': previousData.nbaccomp,
            'userEdit': previousData.userEdit
        };

        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('update-calculator', { '_locale': self.locale }),
                    type: 'POST',
                    data: requestData
                });
                $('[data-container="calculator-wrapper"]').empty().append(response);
                self.$calculator.data('nbpilotes', previousData.nbpilotes);
                self.$calculator.data('nbaccomp', previousData.nbaccomp);
                self.$calculator.data('options', finalOptions);
                self._showUpdateButton();
                self._showInitializeButton();
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })();
    }

    _renderRangeOutput(item, total) {
        $(item).closest('.js-options-row-select').find('[data-container="current-option-number"]').empty().html($(item).val());
        $(item).closest('.js-options-row-select').find('[data-container="total-option-number"]').empty().html('/ ' + total);
    }

    initializeReservationRegister(event) {
        event.preventDefault();
        const $form = $(event.currentTarget);
        const self = this;
        (async($form) => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('initialize-reservation-register'),
                    type: 'POST',
                    data: $form.serialize()
                });
                self.$wrapper.data('user', response.userId);
                self._initializeReservation();

            } catch (jqXHR) {
                if (jqXHR.responseJSON.takenEmail == true) {
                    try {
                        const { value: formValues } = await Swal.fire({
                            title: jqXHR.responseJSON.errorTitle,
                            html: jqXHR.responseJSON.html,
                            focusConfirm: false,
                            confirmButtonText: 'Entrar',
                            preConfirm: () => {
                                return {
                                    'email': document.getElementById('swal-input-email').value = jqXHR.responseJSON.email,
                                    'password': document.getElementById('swal-input-password').value
                                }
                            }
                        });
                        if (formValues) {
                            self._initializeReservationLogin(
                                formValues.email,
                                formValues.password);
                        }
                    } catch (error) {
                        console.error('catch: ', error);
                        self._notifyErrorToUser(error);
                    }
                } else {
                    self
                        .$wrapper
                        .find('#js-card-user')
                        .find('#register')
                        .html(jqXHR.responseJSON.html)
                }
            }
        })($form);

    }

    initializeReservationLogged(event) {
        event.preventDefault();
        this._initializeReservation();
        this._hideInitializeButton();
    }

    _initializeReservationLogin(email, password) {
        let reservationData = this.wrapperData;

        let self = this;
        var formData = {
            email: email,
            password: password,
            _remember_me: true,
            _csrf_token: $("#login_csrf_token").val(),
            dateId: reservationData.dateId,
            nbPilotes: reservationData.nbPilotes,
            nbPassagers: reservationData.nbPassagers,
            options: reservationData.options,
        };

        (async(formData) => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('api_auth_login'),
                    type: 'POST',
                    contentType: "application/json",
                    data: JSON.stringify(formData),
                    xhrFields: {
                        // With every request
                        withCredentials: true
                    }
                });
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: response.icon,
                    title: response.correctLogin
                });
                $('#js-card-invoice').fadeIn(1000);
                self._initializeReservation();
            } catch (jqXHR) {
                const Toast = Swal.mixin({
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: jqXHR.responseJSON.icon,
                    title: jqXHR.responseJSON.checkPassword
                });
            }
        })(formData);
    }

    _initializeReservation() {
        const reservationData = this.$calculator.data();
        let self = this;
        let data = {
            dateId: reservationData.dateId,
            nbAccomp: reservationData.nbaccomp,
            nbPilotes: reservationData.nbpilotes,
            user: reservationData.userId,
            options: reservationData.options
        };
        (async(data) => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('initialize_reservation', { '_locale': self.locale }),
                    method: "POST",
                    data,
                    beforeSend: self._waitForAjax(),
                });
                Swal.close();
                self.$calculatorWrapper.append(response.html);
                self.$wrapper.data('reservation', response.reservationId);
                const swalResponse = await Swal.fire({
                    'html': response.swalHtml,
                    'showCancelButton': true,
                });
                //$('[data-container="js-card-user"]').hide();
                self.$calculator.data('isInitialized', true);
                if (swalResponse.isDismissed !== true && typeof response.codepromo !== 'undefined') {
                    let discount = response.codepromoMontant === null ? response.codepromoPourcentage : response.codepromoMontant;
                    let discountType = response.codepromoMontant === null ? 'pourcentage' : 'ammount';

                    self._addCodePromo(response.codepromoId, response.reservationId);
                    self.$calculator.data('codepromo', response.codepromoId);
                    self.$calculator.data('discount', discount);
                    self.$calculator.data('discount-type', discountType);
                    self.$calculator.data('reservation', response.reservationId);
                    (async() => {
                        try {
                            console.info(self.$calculator.data())
                            const response2 = await $.ajax({
                                url: Routing.generate('update-calculator', { '_locale': self.locale }),
                                type: 'POST',
                                data: self.$calculator.data()
                            });
                            console.info(response2);
                            $('[data-container="calculator-wrapper"]').empty().append(response2);
                            $('.card-logged-user').removeClass('d-none');
                        } catch (jqXHR) {
                            console.info(jqXHR);
                        }
                    })();
                }

                self._loadUserSwitch();
                /* self._loadLoggedInUser(); */
            } catch (jqXHR) {
                if (jqXHR.status == 500) {
                    Swal.fire({
                        'text': 'Error del sistema intentanlo de nuevo. Lamentamos las molestias'
                    });
                }
                if (jqXHR.responseJSON.isReserved == true) {
                    self.$calculator.attr('data-reservation', jqXHR.responseJSON.reservationId);
                    $('#js-card-user').find('.card-body').html(jqXHR.responseJSON.message);
                    const url = Routing.generate('frontend_user_reservation', {
                        'reservation': jqXHR.responseJSON.reservationId
                    });
                    Swal.fire({
                        title: 'Error!',
                        html: jqXHR.responseJSON.message,
                        icon: 'error',
                        confirmButtonText: `<a href="${url}">Ir a la reserva</a>`
                    }).then(() => {
                        self._loadUserSwitch();
                    });
                }
            }
        })(data);
    }

    _addCodePromo(codepromoId, reservationId) {
            (
                async() => {
                    try {
                        const response = await $.ajax({
                            url: Routing.generate('assign-codepromo', { 'codepromo': codepromoId, 'reservation': reservationId }),
                            type: 'GET',
                        });
                    } catch (jqXHR) {
                        console.error(jqXHR);
                    }
                }
            )();
        }
        /* openInvoiceForm(event) {
            event.preventDefault();
            const self = this;
            const reservationId = this.$calculator.data("reservation");

            (async() => {
                try {
                    const invoiceFormResponse = await $.ajax({
                        // ReservationAjaxController
                        url: Routing.generate('ajax_invoice', { 'reservation': reservationId }),
                        type: "GET",
                    });
                    (async(invoiceFormResponse) => {
                        const { value: formValues } = await Swal.fire({
                            title: invoiceFormResponse.title,
                            // reservation/_card_reservation_invoice.html.twig
                            html: invoiceFormResponse.html,
                            focusConfirm: false,
                            preConfirm: () => {
                                return {
                                    name: $('#invoices_name').val(),
                                    nif: $('#invoices_nif').val(),
                                    address: $('#invoices_address').val(),
                                    postalcode: $('#invoices_postalcode').val(),
                                    city: $('#invoices_city').val(),
                                    country: $('#invoices_country').val(),
                                }
                            }
                        }).then((formValues) => {
                            if (formValues.isConfirmed) {
                                self._handleSubmitInvoice(formValues.value);
                            }
                        }).catch((errorSwal) => {
                            console.error(errorSwal);
                            self._notifyErrorToUser(errorSwal);
                        });
                    })(invoiceFormResponse);
                } catch (jqXHR) {
                    console.error(jqXHR);
                    self._notifyErrorToUser(jqXHR);
                }
            })();
        } */

    handleAddTraveller(e) {
        e.preventDefault();
        const self = this;
        const reservationId = this.$calculator.data('reservation');
        const $travellersForm = $("#js-travellers-form");
        const formData = $travellersForm.find('input').serialize();
        const $travellersFormContainers = $travellersForm.find('.js-travellers-form-container');
        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('ajax-add-travellers', { 'reservation': reservationId }),
                    data: formData,
                    method: "POST"
                });
                $('#js-travellers-form').closest('.card-body').html(response.travellersTableHtml);
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })();
    }

    //activated by .js-assign-to-user in _card_add_travellers_data.html within reservationPayment.html.twig
    assignUserDataToTravellerForm(event) {
        event.preventDefault();
        const self = this;
        const $formContainer = $(event.currentTarget).closest('.js-travellers-form-container');

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
                $formContainer.find('[name*="nom"]').val(response.user.nom);
                $formContainer.find('[name*="prenom"]').val(response.user.prenom);
                $formContainer.find('[name*="email"]').val(response.user.email);
                $formContainer.find('[name*="telephone"]').val(response.user.telephone);
            } catch (jqXHR) {
                console.error(jqXHR);
                this._notifyErrorToUser(jqXHR);
            }
        })();
    }

    setReservationStatus(event) {
        event.preventDefault();
        const self = this
        $('#js-reservation-status-statement').html('The reservation has been activated')
        let $checkbox = $(event.currentTarget)
        $(event.currentTarget).closest('.card').addClass('alert-danger')
        const status = this.$wrapper.data('reservationStatus')
        let newStatus = (status == 'cancelled') ? 'initialized' : 'cancelled';
        if (newStatus == "cancelled") {
            self.$wrapper.data('reservationStatus', 'cancelled')
            $(event.currentTarget).closest('.card').addClass('alert-danger')
            $('#js-card-travellers').fadeOut(1000)
            $('.travel-options').fadeOut(1000)
            $('.card-logged-user').fadeOut(1000)
        } else {
            self.$wrapper.data('reservationStatus', 'initialized')
            $(event.currentTarget).closest('.card').removeClass('alert-danger')
            $('#js-card-travellers').removeClass('d-none')
            $('#js-card-travellers').fadeIn(1000)
            $('.travel-options').removeClass('d-none')
            $('.travel-options').fadeIn(1000)
            $('.card-logged-user').fadeIn(1000)
            $('.card-logged-user').closest('.row').removeClass('d-none')
        }
        const reservationId = this.$wrapper.data('reservation-id');
        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('ajax_reservation_set_status', { 'reservation': reservationId }),
                    data: { 'status': newStatus },
                    type: "POST"
                })
                $('#js-reservation-status-statement').html(response.message);
                $('.form-check-label').html(response.label);

            } catch (jqXHR) {
                console.error(jqXHR)
            }
        })();
    }

    applyCodePromo(event) {
        event.preventDefault()
        const self = this;
        const codepromoId = $(event.currentTarget).data('codepromo');
        const reservationId = this.$calculator.data('reservation');

        (
            async() => {
                try {
                    const response = await $.ajax({
                        'url': Routing.generate('apply-code-promo', { 'codepromo': codepromoId }),
                        'data': { 'reservation': reservationId },
                        'type': 'POST',
                    })
                    $('.js-total-price').before(`
                        <tbody>
                            <tr>
                                <th>CODE: ${response.codeTitle}</th>
                                <td align="right">${response.codeMontant} €</td>
                            </tr>
                        </tbody>
                        `);
                    $(e.currentTarget).prop('disabled', true);
                    console.info(response)
                } catch (jqXHR) {
                    console.info(jqXHR)
                }
            }
        )();
    }

    updateChanges(event) {
        event.preventDefault();
        const self = this;
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('ajax-update-changes', {
                            'reservation': self.$calculator.data('reservation')
                        }),
                        data: {
                            'nbPilotes': self.$calculator.data('nbpilotes'),
                            'nbPassagers': self.$calculator.data('nbpassagers'),
                            'options': self.$calculator.data('options')
                        },
                        type: 'POST'
                    })
                    console.info(response)
                } catch (e) {
                    console.error(e)
                }
            }
        )();
    }

    _changeOptionsAmmount(nbPilotes = 0, nbAccomp = 0) {
        console.info('nbPilotes', nbPilotes);
        console.info('nbAccomp', nbAccomp);
        let nbTravellers = parseInt(nbPilotes) + parseInt(nbAccomp);
        console.info('nbTravellers', nbTravellers);
        const self = this;
        this._showUpdateButton();
        this.$wrapper.find('.js-options-row-select input[type="range"]').each((index, element) => {
            $(element).closest('.js-options-row-select').find('[data-container="current-option-number"]').empty().html('0');
            $(element).closest('.js-options-row-select').find('[data-container="total-option-number"]').empty().html('/ ' + nbTravellers);
            $(element).removeAttr('disabled');
            $(element).next().html(nbTravellers);
            $(element).attr('max', nbTravellers);
        });
        /*  this.$wrapper.find('.js-options-row-select select').each((index, element) => {
             self.removeOptions(element);

             for (let i = 0; i <= nbTravellers; i++) {
                 var option = document.createElement("option");
                 option.text = i;
                 element.add(option, element[0]);
             }
         }); */
    }

    /* _handleSubmitInvoice(formData) {
        const reservationId = this.$calculator.data("reservation");
        (async() => {
            try {
                const response = await $.ajax({
                    type: "POST",
                    url: Routing.generate('ajax_save_invoice', { 'reservation': reservationId }),
                    data: formData,
                    datatype: "json",
                    encode: true
                });
                //add swal window with response with downloadable link.
                console.info(response);
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })(formData);
    } */

    _changeOption(reservation, optionData) {
        let url = Routing.generate('user_reservation_change_option', { 'reservation': reservation });
        let data = {
            reservation,
            optionData
        };

        (async() => {
            try {
                const response = await $.ajax({
                    url,
                    method: 'POST',
                    data
                });
                console.info(response);
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })(data)
    }

    removeOptions(selectElement) {
        var i, L = selectElement.options.length - 1;
        for (i = L; i >= 0; i--) {
            selectElement.remove(i);
        }
    }

    updateReservation(event) {
        event.preventDefault();
        const data = this.$wrapper.data();
        const reservationId = this.$wrapper.data('reservation');
        const calculatorData = this.$calculator.data();
        const url = Routing.generate('reservation_payment', { 'reservation': reservationId });
        const href = Routing.generate('frontend_user_reservation', { 'reservation': reservationId });
        (
            async() => {
                try {
                    await $.post(url, {
                        data
                    });
                } catch (error) {
                    console.error(error);
                }
            }
        )();
    }
    _setReservationData(data) {
        this.$calculator.attr('data-nbpilotes', data.nbPilotes);
        this.$calculator.attr('data-nbaccomp', data.nbAccomp);
        this.$calculator.attr('data-reservation', data.reservationId);
        this.$calculator.attr('data-user', data.userId);
        this.$calculator.attr('data-options', JSON.stringify(data.reservationOptions));
        // this.$calculator.attr('data-nb-pilotes', data.nbPilotes);
        // this.$calculator.attr('data-nb-passagers', data.nbPassagers);
        // this.$calculator.attr('data-reservation', data.reservationId);
        // this.$calculator.attr('data-user', data.userId);
        // this.$calculator.attr('data-options', JSON.stringify(data.reservationOptions));
    }

    _getReservationData() {
        let reservationData = [];
        reservationData['nbPilotes'] = this.$calculator.attr('data-nbpilotes');
        reservationData['nbAccomp'] = this.$calculator.attr('data-nbaccomp');
        reservationData['reservationId'] = this.$calculator.attr('data-reservation');
        reservationData['userId'] = this.calculator.attr('data-user');
        if ($calculator.attr('data-options') != "") {
            reservationData['options'] = JSON.parse(this.$calculator.attr('data-options'));
        } else {
            reservationData['options'] = [];
        }
        reservationData['dateId'] = this.$calculator.attr('data-date');
        return reservationData;
    }


    _loadLoggedInUser() {
        this.$wrapper.find('#js-card-user').find('.card-body').empty();
        const reservationId = this.$wrapper.data('reservation');
        const self = this;
        let data = {
            'reservationId': reservationId
        };

        (async(data) => {
            try {
                const response = await $.ajax({
                    method: "POST",
                    url: Routing.generate('ajax_login_result'),
                    data: data,
                });

                $('#js-card-user').find('.card-body').html(response);
                self._loadUserSwitch();
            } catch (jqXHR) {
                console.log("loadloggedinuser Error", jqXHR);
                this._notifyErrorToUser(jqXHR)
            }
        })(data);
    }

    _loadUserSwitch() {
        $("#user-switch").empty();
        (async() => {
            try {
                const response = await $.ajax({
                    type: "GET",
                    url: Routing.generate('ajax_load_user_switch'),
                });
                $("#user-switch").html(response);
            } catch (jqXHR) {
                this._notifyErrorToUser(jqXHR)
            }
        })();
    }

    _notifyErrorToUser(jqXHR) {

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        Toast.fire({
            icon: 'error',
            title: 'Se ha producido un error, lamentamos las molestias.'
        })
    }

    _notifyErrorToAdmin(error) {
        console.log(error);
        //TODO plan ajax to send error via email and notify in logs.
    }

    _notifyErrorTakenEmailToUser(jqXHR) {
        if (jqXHR.responseJSON.takenEmail == true) {
            try {

                Swal.fire('Any fool can use a computer');

            } catch (error) {
                console.error('catch: ', error)
            }
        } else {
            self
                .$wrapper
                .find('#js-card-user')
                .find('#register')
                .html(jqXHR.responseJSON.html)
        }
    }

    _showUpdateButton() {
        if ($('#js-update-changes').hasClass('d-none'))
            $('#js-update-changes').removeClass('d-none').addClass('d-block');
    }

    _showInitializeButton() {
        if ($('[data-action="js-initialize-reservation-logged"]').hasClass('d-none'))
            $('[data-action="js-initialize-reservation-logged"]').removeClass('d-none').addClass('d-block');
    }
    _hideInitializeButton() {
        if ($('[data-action="js-initialize-reservation-logged"]').hasClass('d-block'))
            $('[data-action="js-initialize-reservation-logged"]').removeClass('d-block').addClass('d-none');
    }

    _waitForAjax() {
        Swal.fire({
            title: $('[data-action="js-initialize-reservation-logged"]').data('swal-waiting-title'),
            icon: "info",
            showConfirmButton: false,
            text: $('[data-action="js-initialize-reservation-logged"]').data('swal-waiting-text')
        });
    }

};

export default reservationApp;