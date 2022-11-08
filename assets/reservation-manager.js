import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import reservationCalculator from './reservation-table-calculator.js';


var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

$(document).ready(function() {
    var $wrapper = $('#js-reservation-wrapper'); // main div under which everything happens
    var reservationId = $wrapper.attr('data-reservation-id');

    reservationCalculator($wrapper);
    $wrapper.on('click', '#js-change-nb', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $row = $this.closest('tr');
        var operation = $this.attr('data-operation');
        var nb = $row.find('.js-nb-holder').attr('data-nb');
        nb = reservationCalculator.updateNb(operation, nb);

        $row.find('.js-nb-holder').html(nb);
        $row.find('.js-nb-holder').attr('data-nb', nb);
        $('#js-update-reservation').attr('class', 'btn btn-success');
        $('#js-update-reservation').attr('aria-disabled', 'false');

        /* var data = {
            reservationId: reservationId,
            operation: operation
        };
        $.ajax({
            type: "POST",
            url: "/user/ajax/reservation-manager/changeNb/",
            data: data,
            success: function(result) {
                $this.closest('tr').find('.js-nb-holder').html(result.nb);
            }
        }); */
    });
    $wrapper.on('click', '#js-change-option-ammount', function(e) {
        e.preventDefault();
        var $this = $(this);
        var operation = $(this).attr('data-operation');
        var $row = $(this).closest('tr');
        var ammount = $row.attr('data-option-ammount');
        var optionId = $row.attr('data-option-id');
        var price = $row.attr('data-option-price');
        var optionTitle = $row.attr('data-option-title');

        if (ammount == 0) {
            reservationCalculator.addOption(optionId, price, optionTitle);
            ammount = 1;
        } else {
            ammount = reservationCalculator.updateOption(operation, ammount, optionId);
        }

        $row.attr('data-option-ammount', ammount);
        $row.find('.js-option-ammount-holder').html(ammount);

        $('#js-update-reservation').attr('class', 'btn btn-success');
        $('#js-update-reservation').attr('aria-disabled', 'false');

        /* var data = {
            reservationId: reservationId,
            operation: operation,
            optionId: optionId,
            ammount: ammount
        };

        $.ajax({
            type: "POST",
            url: "/user/ajax/reservation-manager/changeOptionAmmount/",
            data: data,
            success: function(result) {
                $this.closest('tr').attr('data-option-ammount', result.ammount);
                $this.closest('tr').find('.js-option-ammount-holder').html(result.ammount);
            }
        }); */
    });

    $wrapper.on('click', '#js-update-reservation', function() {
        var reservationId = $wrapper.attr('data-reservation-id');
        var data = {};
        var nb = 0;
        var value = 0;
        var field = '';
        var id = 0;
        var options = {};
        var passengers = {};
        reservationCalculator.$wrapper.find('tr.js-calculation-row').each(function(index, element) {
            nb = $(element).attr('data-nb');
            value = $(element).attr('data-value');
            field = $(element).attr('data-field');
            if (field == "option") {
                id = $(element).attr('data-option-id');
                options[index] = {
                    "option": {
                        "id": id,
                        "nb": nb
                    }
                };
                data["options"] = options;
            } else if (field == "pilotes") {
                data["pilotes"] = nb;
            } else {
                console.log("nbpassagers", nb);
                data["passagers"] = nb;
            }
        });
        console.log(data);
        $.ajax({
            type: "POST",
            url: "/user/ajax/reservation-manager/updateReservation/" + reservationId,
            data: data,
            success: function(result) {
                console.log(result);
            }
        });
    });

});