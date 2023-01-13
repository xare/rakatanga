const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';

import Swal from 'sweetalert2';
import { async } from 'regenerator-runtime';

Routing.setRoutingData(routes);

class reservationsApp {
    constructor($wrapper) {

        this.$wrapper = $wrapper;
        this.$wrapper.on(
            'click',
            '.js-cancel-reservation',
            this.cancelReservation.bind(this)
        );
        this.$wrapper.on(
            'click',
            '[data-action="js-update-invoice"]',
            this.openInvoiceForm.bind(this)
        );
        this.$wrapper.on(
            'click',
            '.js-reactivate-reservation',
            this.reactivateReservation.bind(this)
        );
    }
    reactivateReservation(event) {
        event.preventDefault();
        const $reservationCard = $(event.currentTarget).closest('.js-user-reservation-card');
        const reservationId = $reservationCard.data('reservation-id');
        const reservationPreviousStatus = $reservationCard.attr('data-reservation-previous-status');
        (
            async() => {
                await Swal.fire({
                    'title': $(event.currentTarget).data('sweetalert-title'),
                    'text': $(event.currentTarget).data('sweetalert-content')
                }).then((result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = $.ajax({
                                url: Routing.generate('ajax_reservation_set_status', {
                                    'reservation': reservationId
                                }),
                                data: {
                                    status: reservationPreviousStatus
                                },
                                type: "POST"
                            })
                            $reservationCard
                                .find('.reservationStatus')
                                .find('.is-info')
                                .html(reservationPreviousStatus);
                            $reservationCard.removeClass('rakatanga-danger');
                            $reservationCard.find('.btn').removeClass('d-none');
                            $reservationCard.find('.js-cancel-reservation')
                                .removeClass('d-none')
                                .addClass('d-block-inline');
                            $reservationCard.find('.js-reactivate-reservation')
                                .removeClass('d-block-inline')
                                .addClass('d-none');
                        } catch (jqXHR) {
                            console.error(jqXHR)
                        }
                    }

                })
            }
        )();
    }
    cancelReservation(event) {
        event.preventDefault();
        const $reservationCard = $(event.currentTarget).closest('.js-user-reservation-card');;
        const reservationId = $reservationCard.attr('data-reservation-id');;
        const reservationStatus = $reservationCard.attr('data-reservation-status');
        (
            async() => {
                await Swal.fire({
                    'title': $(event.currentTarget).data('sweetalert-title'),
                    'text': $(event.currentTarget).data('sweetalert-content')
                }).then((result) => {
                    if (result.isConfirmed) {

                        try {
                            const ajaxResponse = $.ajax({
                                url: Routing.generate('ajax_reservation_set_status', { 'reservation': reservationId }),
                                data: {
                                    status: 'cancelled'
                                },
                                type: "POST"
                            })
                            $reservationCard.attr('data-reservation-previous-status', reservationStatus);
                            $reservationCard
                                .find('.reservationStatus')
                                .find('.is-info')
                                .html('cancelled');
                            $reservationCard.addClass('rakatanga-danger');
                            $reservationCard.find('.btn').addClass('d-none');
                            $reservationCard
                                .find('.js-reactivate-reservation')
                                .removeClass('d-none')
                                .addClass('d-block-inline');
                        } catch (error) {
                            console.error(error)
                        }
                    }
                });
            }
        )();
    }
    openInvoiceForm(event) {
        event.preventDefault();
        const self = this;
        const invoiceId = $(event.currentTarget)
            .data("invoiceid");;
        (async() => {
            try {
                const invoiceFormResponse = await $.ajax({
                    url: Routing.generate('ajax_invoice', {
                        'invoice': invoiceId
                    }),
                    type: "GET",
                });
                (async(invoiceFormResponse) => {
                    const { value: formValues } = await Swal.fire({
                        title: invoiceFormResponse.title,
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
                                invoiceId: $('#invoices_invoice').val(),
                            }
                        }
                    }).then((formValues) => {
                        if (formValues.isConfirmed) {
                            const row = $(event.currentTarget).closest('tr');
                            const response = self._handleSubmitInvoice(formValues.value, row);
                        }
                    }).catch((errorSwal) => {
                        console.error(errorSwal);
                        //this._notifyErrorToUser(errorSwal);
                    });
                })(invoiceFormResponse);
            } catch (jqXHR) {
                console.error(jqXHR);
                //this._notifyErrorToUser(jqXHR);
            }
        })();
    }

    _handleSubmitInvoice(formData, row) {
        const invoiceId = formData.invoiceId;
        (async() => {
            try {
                const response = await $.ajax({
                    type: "POST",
                    url: Routing.generate('ajax_save_invoice', {
                        'invoice': invoiceId
                    }),
                    data: formData,
                    datatype: "json",
                    encode: true
                });
                row.html(response.html);
                //add swal window with response with downloadable link.
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })(formData);

    }

}

export default reservationsApp;