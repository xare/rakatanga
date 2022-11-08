import $ from 'jquery';
import * as bootstrap from 'bootstrap';

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

$(document).ready(function() {
    var $wrapper = $('.js-reservation-wrapper'); // main div under which everything happens
    var baseUrl = $wrapper.attr('data-baseUrl');
    var prototype = $wrapper.attr('data-prototype'); // symfony prototypes code to generate travellers subforms
    var prixPilote = $wrapper.attr('data-prixMotard'); // Drivers cost
    var prixpassager = $wrapper.attr('data-prixAccomp'); // Codrivers cost
    var dateId = $wrapper.attr('data-date-id'); // Id of the date
    var reservationId = $wrapper.attr('data-reservation-id');


    if (reservationId == '') {
        $("#js-card-invoice").hide();
        $("#js-card-payment").hide();
    }
    //formatter variable...
    var formatter = new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',

        // These options are needed to round to whole numbers if that's what you want.
        //minimumFractionDigits: 0, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
        //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
    });

    /*****************************************************/
    /* FUNCTIONS                                         */
    /*****************************************************/

    ////////////////////////////////////////////////////////////////////
    // Calculate the Total of the Reservation                        //
    // This total is changed every time a traveller or a option is    //
    // added                                                          //
    ////////////////////////////////////////////////////////////////////
    function calculateTotal(element) {
        var totalValue = 0; // the value that will be returned in the end by the function
        var rowValue = 0; // the price of the item
        var rowNb = 0; // the number of items the value has to be multiplied by
        // we loop of the rows of the table which contains the data.
        element.find("tr.js-calculation-row").each(function() {
            rowNb = $(this).attr('data-nb');
            rowValue = $(this).attr('data-value');
            totalValue += rowNb * rowValue;
        });
        return totalValue;
    }
    /////////////////////////////////////////////////////////////////////////////
    // Loop all the rows and if the options are present return true else false //
    /////////////////////////////////////////////////////////////////////////////
    function checkRows(element, id) {
        var IdArray = [];
        element
            .find(".js-calculation-option-row")
            .each(function(index) {
                IdArray[index] = $(this).attr('data-option-selected-id');
            });
        if (IdArray.find(element => element == id) != null) {
            return true;
        } else {
            return false;
        }
    }

    ////////////////////////////////////////////////////////////////////
    // element= $wrapper.find('.js-options-row-select')               //
    ////////////////////////////////////////////////////////////////////
    function checkAllOptionsNull(element) {
        var isNull = true;
        element.each(function(index) {
            var optionNb = $(this).find('select').val();
            if (optionNb != 0) {
                isNull = false;
            }
        });
        return isNull;
    }

    function initializeButton() {
        // ACTIVATE INITIALISE BUTTON
        if ($('.js-add-passagers').val() == 0 && $('.js-add-pilotes').val() == 0) {
            $('#js-initialize-reservation').attr('class', 'btn btn-secondary disable');
            $('#js-initialize-reservation').attr('aria-disabled', 'true');
        } else if ($('.js-add-passagers').val() > 0 || $('.js-add-pilotes').val() > 0) {
            $('#js-initialize-reservation').attr('class', 'btn btn-success');
            $('#js-initialize-reservation').attr('aria-disabled', 'false');
        }
    }

    function showRegisterForm(baseUrl) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/register",
            success: function(result) {
                $("#registerform").find('.modal-content').html(result);
            }
        });
    }

    function processRegistration(baseUrl, event) {
        var $form = $(event.currentTarget);
        $.ajax({
            type: "POST",
            url: $form.attr('action'),
            data: $form.serialize(),
            success: function(result) {
                $("#registerform").find('.modal-body').html(result);
            },
            error: function(jqXHR) {
                $("#registerform").find('.modal-content').html(jqXHR.responseText);
            }
        });
    }

    function loadUserSwitch(baseUrl) {
        $("#user-switch").empty();
        $.ajax({
            type: "GET",
            url: baseUrl + "/ajax/load-user-switch",
            success: function(result) {
                $("#user-switch").html(result);
            }
        });
    }

    function activateAddTravellersButton() {
        var nbpilotes = $wrapper.attr('data-nbPilotes');
        var nbpassagers = $wrapper.attr('data-nbPassagers');
        var total = parseInt(nbpilotes) + parseInt(nbpassagers);
        if (total > 1) {
            $('#js-button-travellers').show();
        } else {
            $('#js-button-travellers').hide();
        }
    }

    /**************************************************** */
    /* ACTIONS                                            */
    /**************************************************** */

    ////////////////////////////////////////////////////////
    // REMOVE TRAVELLERS' CARDS                           //
    // Removes extra travellers' card at each click on    //
    // the delete button topleft.                         //
    ////////////////////////////////////////////////////////

    $wrapper.on('click', '.js-remove-traveller', function(e) {
        e.preventDefault();

        $(this).closest('.js-traveller-item')
            .fadeOut()
            .remove();
    });
    /* $wrapper.on('click', '.js-traveller-add', function(e) {
        e.preventDefault();
        // Get the data-prototype explained earlier
        var prototype = $wrapper.data('prototype');
        // get the new index
        var index = $wrapper.data('index');
        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);
        // increase the index with one for the next item
        $wrapper.data('index', index + 1);
        // Display the form in the page before the "new" link
        $(this).before(newForm);
    }); */

    /****************************************/
    /* ADD PILOTS                           */
    /****************************************/
    $wrapper.on('change', '.js-add-pilotes', function(e) {
        e.preventDefault();
        $('.js-pilot-item').remove();

        $wrapper.attr('data-nbPilotes', this.value);
        var totalPricePilote = parseInt(this.value * prixPilote);
        var totalPricePassager;
        var index;


        /*--------------------------------------------------*/
        //  RIGHT COLUMN SUMMARY: UPDATE TOTAL PRICES       //   
        /*--------------------------------------------------*/

        $('#js-pilotes-row').attr('data-nb', this.value);
        $('#js-pilotes-row').attr('data-value', prixPilote);
        $('#js-total-price-pilote').html(formatter.format(totalPricePilote));
        $('#js-total-price-pilote').attr('data-total-price-pilote', totalPricePilote);
        $('#js-total-pilote').html(this.value);
        // ACTIVATE INITIALISE BUTTON
        initializeButton();

        var totalPriceReservation = calculateTotal($('#js-card-calculations'));
        $('#js-total-price-reservation').html(formatter.format(totalPriceReservation));
    });

    /****************************************/
    /* ADD PASSENGERS                       */
    /****************************************/
    $wrapper.on('change', '.js-add-passagers', function(e) {
        e.preventDefault();
        $('.js-passenger-item').remove();
        $wrapper.attr('data-nbPassagers', this.value);
        var totalPricePilote = parseInt($('#js-total-price-pilote').attr('data-total-price-pilote'));
        var totalPricePassager = this.value * prixpassager;

        /*--------------------------------------------------*/
        //  RIGHT COLUMN SUMMARY: UPDATE TOTAL PRICES       //   
        /*--------------------------------------------------*/

        $('#js-total-price-passager').html(formatter.format(totalPricePassager));
        $wrapper.attr('data-total-price-passager', totalPricePassager);
        $('#js-total-passager').html(this.value);
        $('#js-currency').removeClass('d-none');

        $('#js-passagers-row').attr('data-nb', this.value);
        $('#js-passagers-row').attr('data-value', prixpassager);

        // ACTIVATE INITIALISE BUTTON
        initializeButton();

        var totalPriceReservation = calculateTotal($('#js-card-calculations'));
        $('#js-total-price-reservation').html(formatter.format(totalPriceReservation));
        $('#js-total-price-reservation').data('total-price-reservation', totalPriceReservation);
    });

    /****************************************/
    /* ADD OPTIONS                          */
    /****************************************/
    $wrapper.on('change', '.js-add-option', function(e) {
        e.preventDefault();
        /****************************************/
        /* VARIABLE DECLARATIONS                */
        /****************************************/
        var price = $(this).closest("tr").attr('data-option-price');
        var title = $(this).closest("tr").attr('data-option-title');
        var id = $(this).closest("tr").attr('data-option-id');
        var value = this.value;
        var totalPriceReservation = parseInt($wrapper.attr('data-total-price-reservation'));
        var totalPriceOptions = 0;
        var renderRow = "<tr class='js-calculation-row js-calculation-option-row' data-nb='" + value + "' data-value='" + price + "' data-option-selected-id='" + id + "'><td><span class='js-total-options'>" + value + "</span> &#10005; " + title + "</td><td class='js-option-price'><span>" + formatter.format(parseInt(price * value)) + "</span></td></tr>";

        /**ADD DATA TO THE WRAPPER DATA ATTRIBUTES */
        $wrapper.attr('data-option-title-' + id, value);

        /*--------------------------------------------------*/
        //  RIGHT COLUMN SUMMARY: UPDATE TOTAL PRICES       //   
        /*--------------------------------------------------*/

        if ($('.js-calculation-option-row').length == 0 && value != 0) {
            $("#js-card-calculations").find('tbody').append(renderRow);
        } else {
            var checkRowsValue = checkRows($("#js-card-calculations"), id);
            if (checkRowsValue == true) {
                //there is no need to create a new row.
                $("#js-card-calculations")
                    .find(".js-calculation-option-row")
                    .each(function(index) {

                        if (id == $(this).attr('data-option-selected-id')) {
                            if (value == 0) {
                                $(this).remove();
                            } else {
                                $(this).find('.js-total-options').html(value);
                                $(this).attr('data-nb', value);
                                $(this).find('.js-option-price span').html(formatter.format(parseInt(price * value)));
                                $(this).find('.js-option-price').attr('data-total-price-option-' + index, parseInt(price * value));
                            }
                        }
                    });
            } else {
                if (value != 0) {
                    $("#js-card-calculations").find('tbody').append(renderRow);
                }
            }
        }
        totalPriceReservation = calculateTotal($("#js-card-calculations"));
        $('#js-total-price-reservation').html(formatter.format(totalPriceReservation));
        $wrapper.attr('data-total-price-reservation', totalPriceReservation);
    });

    //////////////////////////////////////////////
    // RESERVATION INITIALIZE                  //
    //////////////////////////////////////////////
    $wrapper.on('click', '#js-initialize-reservation', function(event) {
        event.preventDefault();
        var button = $(this);
        button.prepend("<i class='fa fa-spinner'></i>");

        var nbPilotes = $wrapper.attr('data-nbPilotes');
        var nbPassagers = $wrapper.attr('data-nbPassagers');
        var locale = $wrapper.attr('data-locale');
        var userId = $wrapper.attr('data-user-id');
        var options = {};
        var optionId;
        var optionNb;

        if (userId != '') {
            $('#js-card-invoice').fadeIn(1000);
            $("#js-card-payment").fadeIn(1000);
        }
        activateAddTravellersButton();

        if (checkAllOptionsNull($wrapper.find('.js-options-row-select')) == true) {
            options = null;
        } else {
            $wrapper.find('.js-options-row-select').each(function(index) {
                optionId = $(this).attr('data-option-id');
                optionNb = $(this).find('select').val();
                if (optionNb > 0) {
                    options[optionId] = parseInt(optionNb);
                }
            });
        }
        //console.log(options);
        var formData = {
            dateId: dateId,
            locale: locale,
            nbPilotes: nbPilotes,
            nbPassagers: nbPassagers,
            options: options,
        };
        $.ajax({
            type: "POST",
            url: baseUrl + "/ajax/initialize/reservation",
            data: formData,
            error: function(jqXHR, status, error) {
                console.log(status, error);
            },
            success: function(result) {
                console.log(result.user);
                if (result.isReserved == true) {
                    $("#js-reservation-form .form-control ").prop('disabled', true);
                    $("#js-card-travellers").addClass("text-muted").css({ "background": "#eaeaea" });
                    $("#js-card-options").css({ "background": "#eaeaea" }).find("td").addClass("text-muted");
                    $("#js-card-invoice").hide();
                    $("#js-card-payment").html('Ya has realizado una reserva');

                } else {
                    button.find('i').remove();
                    button.fadeOut(1000);
                    $("#js-reservation-form .form-control ").prop('disabled', true);
                    $("#js-card-travellers").addClass("text-muted").css({ "background": "#eaeaea" });
                    $("#js-card-options").css({ "background": "#eaeaea" }).find("td").addClass("text-muted");
                    $wrapper.attr('data-reservation-id', result.reservationId);
                    $wrapper.attr('data-user-id', result.userId);
                    $('#js-button-payment').attr('href', baseUrl + '/reservation/' + result.reservationId + '/payment')
                    $('#js-card-login').fadeIn(1000);
                }
            },
        });
    });

    //////////////////////////////////////////////
    // RESERVATION LOGIN                        //
    //////////////////////////////////////////////

    $wrapper.on('click', '#js-reservation-login-button', function(event) {
        event.preventDefault();
        var reservationId = $wrapper.attr('data-reservation-id');
        var myModal = new bootstrap.Modal($('#loginform'));
        var formData = {
            _username: $("#loginEmail").val(),
            _password: $("#loginPassword").val(),
            _csrf_token: $("#login_csrf_token").val(),
            reservationId: reservationId
        };
        $.ajax({
            type: "POST",
            url: baseUrl + "/ajax/login",
            data: formData,
            dataType: "json",
            encode: true,
            success: function(result) {
                $('#js-card-invoice').fadeIn(1000);
                var nbPilotes = $wrapper.attr('data-nbPilotes');
                var nbPassagers = $wrapper.attr('data-nbPassagers');
                var travelData = {
                    nbPilotes: nbPilotes,
                    nbPassagers: nbPassagers,
                    reservationId: reservationId,
                };
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/ajax/login-result",
                    data: travelData,
                    success: function(result2) {
                        $("#loginform").find('.modal-body').empty();
                        $("#loginform").find('.modal-body').html(result2);

                        $.ajax({
                            type: "POST",
                            url: baseUrl + "/ajax/load-login-card",
                            data: { reservationId: reservationId },
                            success: function(result3) {
                                myModal.hide();
                                $('.modal-backdrop').remove();
                                $("#js-card-login").find('.card-body').html(result3);
                                loadUserSwitch(baseUrl);
                            }
                        });
                    }
                });
            },
        });
    });



    //////////////////////////////////////////////
    // REGISTRATION                             //
    //////////////////////////////////////////////
    $wrapper.on('click', '#js-reservation-registration-button', function(event) {
        event.preventDefault();
    });

    $wrapper.on('click', '#js-button-travellers', function(event) {
        event.preventDefault();
        var nbpilotes = $wrapper.attr("data-nbpilotes");
        var nbpassager = $wrapper.attr("data-nbpassagers");
        var dateId = $wrapper.attr("data-date-id");
        var locale = $wrapper.attr("data-locale");
        var reservationId = $wrapper.attr("data-reservation-id");
        var index;
        var index2;
        var prototype = $wrapper.attr('data-prototype');
        $('#js-card-add-travellers').fadeIn(1000);
        $("#js-text-nbpilotes").html(nbpilotes);
        $("#js-text-nbpassagers").html(nbpassager);
        /*----------------------------*/
        //  CREATE TRAVELLERS' FORMS  //
        /*----------------------------*/
        for (index = 0; index < nbpilotes - 1; index++) {
            var newForm = prototype.replace(/__name__/g, index);
            newForm = newForm.replace('<option value="driver">', '<option value="driver" selected>');
            $('#js-pilots-forms').append(newForm);
            $('.js-traveller-item').addClass('js-pilot-item');
        }

        $('.traveller-type').html('Piloto');
        /*----------------------------*/
        //  CREATE TRAVELLERS' FORMS  //
        /*----------------------------*/
        for (index2 = 0; index2 < nbpassager; index2++) {
            var newForm2 = prototype.replace(/__name__/g, index);
            newForm2 = newForm2.replace('<option value="codriver">', '<option value="codriver" selected>');

            $('#js-passengers-forms').append(newForm2);
            $('.js-traveller-item').addClass('js-passenger-item');
        }
        $('.traveller-type').html('Acompañante');
        $('#js-travellers-forms').append(`<a class="btn btn-primary" href="${locale}/reservation/${dateId}/makepayment" id="js-reservation-make-payment">Make a payment</a>`);
    });

    $wrapper.on('click', '#js-open-login-form', function(event) {
        event.preventDefault();
        $("#registerform").fadeOut(1000, function() {
            $(".login-form").fadeIn(1000);
        });

    });
    $wrapper.on('click', '#js-open-registration-form', function(event) {
        event.preventDefault();
        showRegisterForm(baseUrl);
    });


    $wrapper.on('submit', '#js-registration-user-form', function(event) {
        event.preventDefault();
        var reservationId = $wrapper.attr('data-reservation-id');
        processRegistration(baseUrl, event);
    });

    $wrapper.on('click', '#js-button-verify', function(event) {
        event.preventDefault();
        var reservationId = $wrapper.attr('data-reservation-id');
        var nbPilotes = $wrapper.attr('data-nbPilotes');
        var nbPassagers = $wrapper.attr('data-nbPassagers');
        var formData = {
            verification: $('#form_verification').val(),
            userId: $('#form_userId').val()
        }

        var travelData = {
            nbPilotes: nbPilotes,
            nbPassagers: nbPassagers,
            reservationId: reservationId
        };
        $.ajax({
            url: baseUrl + "/ajax/verify",
            type: "POST",
            data: formData,
            success: function(result) {
                $("#registerform").find('.modal-body').html(result);
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/ajax/login-result",
                    data: travelData,
                    success: function(result2) {
                        console.log(result2);
                        $('#js-card-login').find('.card-body').html(result2);
                        //$('#registerform').modal('hide');
                        $('#js-card-invoice').fadeIn(1000);
                        $('#js-')
                        loadUserSwitch(baseUrl);
                    }
                });
            }
        });
    });
    /* $wrapper.on('click', '#js-button-payment', function(event) {
        event.preventDefault();
        var reservationId = $wrapper.attr('data-reservation-id');
        var dateId = $wrapper.attr("data-date-id");
        var data = {
                date: dateId,
                reservationId: reservationId
            } */
    /* $.ajax({
        type: "POST",
        url: "/reservation/" + reservationId + "/payment",
        data: data,
        success: function(result) {
            alert("hello");
        }

    }); */
    /* }); */
    $wrapper.on('click', '#js-invoice-question-yes', function(event) {
        event.preventDefault();
        var reservationId = $wrapper.attr("data-reservation-id");
        $.ajax({
            type: "GET",
            url: baseUrl + '/ajax/invoice/' + reservationId,
            success: function(result) {
                $("#js-invoice-form").show().html(result);
            }
        });
    });
    $wrapper.on('click', '#js-invoice-question-no', function(event) {
        event.preventDefault();
        $('#js-invoice-form').hide();

    });

    $wrapper.on('click', '#js-submit-invoice', function(event) {
        event.preventDefault();
        var reservationId = $('#js-invoice-user').attr('data-reservation-id');
        var myModal = new bootstrap.Modal($('#createInvoice'));

        var formData = {
            name: $('#invoices_name').val(),
            nif: $('#invoices_nif').val(),
            address: $('#invoices_address').val(),
            postalcode: $('#invoices_postalcode').val(),
            city: $('#invoices_city').val(),
            country: $('#invoices_country').val(),
        };
        $.ajax({
            type: "POST",
            url: baseUrl + "/ajax/save-invoice/" + reservationId,
            data: formData,
            datatype: "json",
            encode: true,
            success: function(result) {
                myModal.hide();
                $('.modal-backdrop').remove();
                $('#js-card-invoice').empty().html(result);
            }
        });
    });
    $wrapper.on('click', '#js-invoice-user', function(event) {
        event.preventDefault();
        $.ajax({
            type: "GET",
            url: baseUrl + "/ajax/import-user-data/",
            success: function(result) {
                console.log(result);
                $("#invoices_name").val(result.name);
                $("#invoices_city").val(result.city);
                $("#invoices_country").val(result.country);
            }
        });
    });
});