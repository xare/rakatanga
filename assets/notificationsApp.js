const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
//import reservationCalculator from './reservation-table-calculator.js';
import Swal from 'sweetalert2';

Routing.setRoutingData(routes);

class NotificationsApp {

    constructor($wrapper) {
        this.wrapper = $wrapper;
    }

    _getLatestReservation(callback) {
        return new Promise((resolve, reject) => {
            (async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('notifications-latest-reservation'),
                        method: 'GET',
                    });
                    this.wrapper.data('reservation', response.reservation.id);
                    resolve(response.reservation.id);
                } catch (error) {
                    console.error(error);
                    reject(error);
                }
            })();
        });
    }
    async _isDifferentReservation() {
        const actualLatestReservation = this.wrapper.data('reservation');
        try {
            const latestReservation = await this._getLatestReservation();

            if (latestReservation === actualLatestReservation) {
                return false;
            } else {
                return true;
            }
        } catch (error) {
            console.error(error);
            reject(error);
        }
    }
    notifyNewReservation() {
        (async() => {
            try {
                const isDifferentReservation = await this._isDifferentReservation();
                console.info(isDifferentReservation);
                if (isDifferentReservation) {
                    Swal.fire({
                        title: "New Reservation",
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            } catch (error) {
                console.error(error);
            }
        })();
    }


}

export default NotificationsApp;