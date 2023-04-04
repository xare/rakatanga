const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
//import reservationCalculator from './reservation-table-calculator.js';
import Swal from 'sweetalert2';
import {
    data
} from 'jquery';
import travellersApp from './travellersApp.js';
Routing.setRoutingData(routes);

class reservationApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$calculator = $('[data-container="calculator"]');
        this.$calculatorWrapper = $('[data-container="calculator-wrapper"]');
        this.$initializeButton = this.$calculator.find('[data-action="js-initialize-reservation-logged"]');
        this.wrapperData = this.$wrapper.data();
        this.locale = $('html').attr('lang');
        this.travellersApp = new travellersApp();
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
            '.js-options-row-select select',
            this.handleAddOption.bind(this)
        )

        this.$wrapper.on(
            'submit',
            '[data-action="js-register-form"]',
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

        /* this.$wrapper.on(
            'click',
            '#js-add-travellers',
            this.travellersApp.handleAddTraveller.bind(this)
        ) */
        this.$wrapper.on(
            'click',
            '[data-action="js-add-travellers"]',
            this.travellersApp.handleAddTraveller.bind(this.travellersApp)
        )
        this.$wrapper.on(
            'click',
            '[data-action="js-assign-to-user"]',
            this.travellersApp.assignUserDataToTravellerForm.bind(this.travellersApp)
        );

        /* this.$wrapper.on(
            'click',
            '#js_reservation_status_initialized',
            this.setReservationStatus.bind(this)
        ) */

        this.$wrapper.on(
            'click',
            '[data-action="js-apply-codepromo"]',
            this.applyCodePromo.bind(this)
        )

        this.$wrapper.on(
            'click',
            '[data-action="updateReservation"]',
            this.updateReservation.bind(this)
        )

        this.$wrapper.on(
            'click',
            '[data-action="update-changes"]',
            //this.updateReservation.bind(this)
            this.updateChanges.bind(this)
        )
        this.$wrapper.on(
            'click',
            '[data-action="js-add-codespromo"]',
            this.addCodesPromo.bind(this)
        )

        this.$wrapper.on(
            'click',
            '[data-action="js-open-traveller-form"]',
            this.travellersApp.openTravellersSwalForm.bind(this.travellersApp)
        )
        this.$wrapper.on(
            'submit',
            '[data-action="js-open-payment-page"]',
            this.openPaymentPage.bind(this)
        )

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
        let data = this.$calculator.data();

        data.locale = $('html').attr('lang');
        $('[data-container="nb' + type + '"]').empty().html(nb);
        if (data.reservation) {
            this.travellersApp._addTravellersForms(
                data.reservation,
                data.nbpilotes,
                data.nbaccomp);
            this._updateReservation(
                data.reservation,
                data.nbpilotes,
                data.nbaccomp)
        }
        const self = this;
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('update-calculator', {
                            '_locale': self.locale,
                        }),
                        type: 'POST',
                        beforeSend: () => {
                            $('[data-container="calculator"]').find('.card-body > div > table').hide();
                            let waitingText = $('[data-container="calculator"]').data('waiting');
                            $('[data-container="calculator"]').find('.card-body').append(`<div><i class="fas fa-spinner fa-spin"></i> ${waitingText}`);
                        },
                        data
                    });
                    $('[data-container="calculator-wrapper"]').empty().append(response);
                    if (data.nbpilotes != 0)
                        self.$calculator.data('nbpilotes', data.nbpilotes);
                    if (data.nbaccomp != 0)
                        self.$calculator.data('nbaccomp', data.nbaccomp);
                    self._showUpdateButton();
                    self._showInitializeButton();
                    $('[data-container="js-card-user"]').removeClass('d-none');
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            }
        )();
    }

    _updateReservation(
        reservation,
        nbpilotes = 0,
        nbaccomp = 0,
        options = []) {
        const locale = this.locale;
        const data = {
            reservation,
            nbpilotes,
            nbaccomp,
            options,
            isInitialized: this.$calculator.data('isInitialized'),
            '_locale': locale
        };

        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('ajax-update-changes', {
                            reservation,
                            '_locale': locale
                        }),
                        method: 'POST',
                        data
                    });
                    console.info(response);
                    $('#calculator-wrapper').append(response);
                } catch (error) {
                    console.error(error);
                }
            }
        )();
    }

    _filterOptions(previousOptions, currentOption) {
        let finalOptions = [];
        if (previousOptions.every((previousOption) => { //returns true when then new option selected was not part of the previousOptions
                return (previousOption.id !== null && previousOption.id !== currentOption.id)
            })) {
            finalOptions = [
                ...previousOptions,
                ...[currentOption]
            ];
        } else { // when the option was already selected but when we change the ammount for that option.
            ;
            previousOptions.forEach((previousOption) => {
                if (previousOption.id == currentOption.id)
                    return (previousOption.ammount = parseInt(currentOption.ammount));
            });
            finalOptions = previousOptions;
        }
        return finalOptions;
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

        this._renderRangeOutput($(event.currentTarget), $(event.currentTarget).attr('max'));
        let date = {
            date: previousData.date
        };
        let currentOption = {
            id: eval(matches[0]),
            ammount,
            price: optionPrice,
            title: optionTitle
        };
        let finalOptions = this._filterOptions(previousOptions, currentOption);
        let requestData = {
            ...date,
            'options': finalOptions,
            reservation,
            'isInitialized': previousData.isInitialized,
            'nbpilotes': previousData.nbpilotes,
            'nbaccomp': previousData.nbaccomp,
            'userEdit': previousData.userEdit
        };
        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('update-calculator', {
                        '_locale': self.locale
                    }),
                    type: 'POST',
                    beforeSend: () => {
                        $('[data-container="calculator"]').find('.card-body > div > table').hide();
                        let waitingText = $('[data-container="calculator"]').data('waiting');
                        $('[data-container="calculator"]').find('.card-body').append(`<div><i class="fas fa-spinner fa-spin"></i> ${waitingText}`);
                    },
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
                    url: Routing.generate(
                        'initialize-reservation-register', { '_locale': self.locale }
                    ),
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

        const comment = this.$wrapper.find('[name="reservation_comment]').val();
        $("[data-action='handle-change-nb-pilotes']").prop("disabled", true);
        $("[data-action='handle-change-nb-accomp']").prop("disabled", true);
        $("tr.js-options-row-select select").prop("disabled", true);
        let self = this;
        let data = {
            dateId: reservationData.date,
            nbAccomp: reservationData.nbaccomp,
            nbPilotes: reservationData.nbpilotes,
            user: reservationData.userId,
            options: reservationData.options,
            comment
        };
        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('initialize_reservation', {
                        '_locale': self.locale
                    }),
                    method: "POST",
                    data,
                    beforeSend: self._waitForAjax(),
                });
                Swal.close();
                self.$calculatorWrapper.append(response.html);
                self.$wrapper.data('reservation', response.reservationId);
                /* const swalResponse = await Swal.fire({
                    'html': response.swalHtml,
                    'showCancelButton': true,
                }); */
                self.$calculator.data('isInitialized', true);
                self.$calculator.data('reservation', response.reservationId);
                self._loadUserSwitch();
                $("[data-container='js-card-user']").hide();
                $('.card-codespromo, .card-comment').removeClass('d-none');
                $('.card-codespromo').html(response.codespromoHtml);
                this.travellersApp._addTravellersForms(
                    response.reservationId,
                    data.nbPilotes,
                    data.nbAccomp);
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
                        'reservation': jqXHR.responseJSON.reservationId,
                        '_locale': self.locale
                    });
                    Swal.fire({
                        title: jqXHR.responseJSON.title,
                        html: jqXHR.responseJSON.message,
                        icon: 'error',
                        confirmButtonText: `<a href="${url}">${jqXHR.responseJSON.confirmButtonText}</a>`,
                    }).then(() => {
                        self._loadUserSwitch();
                    });
                }
            }
        })();
    }

    updateChanges(event) {
        event.preventDefault();

        $(event.currentTarget).removeClass('d-block');
        $(event.currentTarget).hide();
        this._updateChanges();
    }

    _updateChanges() {
        const self = this;

        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('ajax-update-changes', {
                            'reservation': self.$wrapper.data('reservation'),
                            '_locale': self.locale
                        }),

                        data: {
                            'nbpilotes': self.$calculator.data('nbpilotes'),
                            'nbaccomp': self.$calculator.data('nbaccomp'),
                            'options': self.$calculator.data('options'),
                            'codespromo': self.$calculator.data('codespromo'),
                        },
                        type: 'POST'
                    })

                    $('[data-container="calculator-wrapper"]').append(response.html);

                } catch (e) {
                    console.error(e)
                }
            }
        )();
    }
    _updateCalculator() {
        const self = this;
        const isTravellersAdded = ($('[data-container="calculator-wrapper"]').data('travellers-added') === 'yes') ? true : false;
        console.info(isTravellersAdded);
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('update-calculator', {
                            '_locale': self.locale,
                        }),
                        data: {...self.$calculator.data(), isTravellersAdded },
                        method: 'POST'
                    });
                    $('[data-container="calculator-wrapper"]').empty().append(response);
                } catch (error) {
                    console.info(error);
                }
            }
        )();
    }

    _changeOptionsAmmount(nbPilotes = 0, nbAccomp = 0) {
        let nbTravellers = parseInt(nbPilotes) + parseInt(nbAccomp);
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



    _changeOption(reservation, optionData) {
        let url = Routing.generate('user_reservation_change_option', {
            'reservation': reservation
        });
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
        const url = Routing.generate('reservation_payment', {
            'reservation': reservationId
        });
        const href = Routing.generate('frontend_user_reservation', {
            'reservation': reservationId
        });
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
    openPaymentPage(event) {
        event.preventDefault();
        const textarea = document.getElementById('reservation-comment');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'comment';
        input.value = textarea.value;
        const form = event.currentTarget;
        form.appendChild(input);
        form.submit();
    }

    addCodesPromo(event) {
        event.preventDefault();
        const reservationId = this.$wrapper.data('reservation');
        const $currentTarget = $(event.currentTarget);
        const $inputElement = $currentTarget.closest('[data-container="codepromo-input"]').find('input');
        const self = this;
        const codespromo = $inputElement.val();
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('reservation-addCodesPromo'),
                        method: "POST",
                        data: {
                            reservationId,
                            codespromo
                        }
                    });
                    this.$calculator.data('codespromo', codespromo);
                    this.$calculator.data('reservation', reservationId);
                    //
                    if (response.codeStatus === "success") {
                        this._updateChanges();
                        this._updateCalculator();
                    } else {
                        Swal.fire({
                            title: response.message
                        })
                    }

                } catch (error) {
                    console.info(error);
                }
            }
        )();
    }

    applyCodePromo(event) {
        event.preventDefault();
        const self = this;
        const codepromo = $(event.currentTarget).data('codepromo');
        const date = this.$calculator.data('date');
        const reservation = this.$calculator.data('reservation');
        const isInitialized = this.$calculator.data('isInitialized');
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('apply-code-promo', {
                            codepromo
                        }),
                        type: 'POST',
                        data: { reservation }
                    });
                    (async() => {
                        try {
                            const response2 = await $.ajax({
                                url: Routing.generate('update-calculator', {
                                    '_locale': self.locale
                                }),
                                type: 'POST',
                                data: {
                                    reservation,
                                    date,
                                    codepromo,
                                    isInitialized
                                },
                                beforeSend: () => {
                                    $('[data-container="calculator"]').find('.card-body > div > table').hide();
                                    let waitingText = $('[data-container="calculator"]').data('waiting');
                                    $('[data-container="calculator"]').find('.card-body').append(`<div><i class="fas fa-spinner fa-spin"></i> ${waitingText}`);
                                },
                            });
                            $('[data-container="calculator-wrapper"]').empty().append(response2);
                            $('.card-logged-user').removeClass('d-none');
                            self.$calculator.data('codespromo', codepromo);
                        } catch (jqXHR) {
                            console.error(jqXHR)
                        }
                    })();

                    /* $('[data-container="total-calculator"]').before(response.rowCodePromoHtml); */
                    $(event.currentTarget).prop('disabled', true);

                } catch (jqXHR) {
                    console.error(jqXHR)
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
                console.error("loadloggedinuser Error", jqXHR);
                this._notifyErrorToUser(jqXHR)
            }
        })(data);
    }

    _loadUserSwitch() {
        $("#user-switch").empty();
        const self = this;
        (async() => {
            try {
                const response = await $.ajax({
                    type: "GET",
                    url: Routing.generate('ajax_load_user_switch', { '_locale': self.locale }),
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
        if ($('[data-action="update-changes"]').hasClass('d-none'))
            $('[data-action="update-changes"]').removeClass('d-none').addClass('d-block');
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
            title: this.$calculator.data('swalWaitingTitle'),
            icon: "info",
            showConfirmButton: false,
            text: this.$calculator.data('swalWaitingText')
        });
    }

};

export default reservationApp;