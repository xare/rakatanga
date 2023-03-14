const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';
import reservationApp from './reservationApp.js';

class codespromoApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$calculator = $('[data-container="calculator"]');
        this.locale = $('html').attr('lang');
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
                    this.reservationApp._updateChanges();
                    this.reservationApp._updateCalculator();
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
                        } catch (jqXHR) { console.info(jqXHR) }
                    })();

                    /* $('[data-container="total-calculator"]').before(response.rowCodePromoHtml); */
                    $(event.currentTarget).prop('disabled', true);

                } catch (jqXHR) {
                    console.info(jqXHR)
                }
            }
        )();
    }
    _addCodePromo(codepromoId, reservationId) {
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('assign-codepromo', {
                            'codepromo': codepromoId,
                            'reservation': reservationId
                        }),
                        type: 'GET',
                    });
                    return response;
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            }
        )();
    }
}

export default codespromoApp;