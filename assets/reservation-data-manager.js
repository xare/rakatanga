import DocumentsList from './documents.js';
import 'bootstrap-datepicker';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.fr.js';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.en-GB.js';
require('airport-autocomplete-js');
import reservationDataApp from './reservationDataApp.js';

$(document).ready(function() {
    let $wrapper = $('#js-reservation-wrapper');
    var ReservationDataApp = new reservationDataApp($wrapper);


    var documentsList = new DocumentsList($wrapper);
    var types = [
        'passport', 'visa', 'drivers'
    ];
    types.map((type) => {
        documentsList.initializeDropzone(type, documentsList.travellerId);
    });
    let locale = $wrapper.data('locale');
    console.info(locale);
    $('.input-daterange').datepicker({
        format: 'dd-mm-yyyy',
        language: locale
    });

    const airportOptions = {
        formatting: `<div class="$(unique-result)"
                     single-result" 
                     data-index="$(i)"> 
                   <strong>$(IATA)</strong> $(name) ($(city) - $(country)) </div>`,
        fuse_options: {
            shouldSort: true,
            threshold: 0.4,
            maxPatternLength: 32,
            keys: [{
                    name: "country",
                    weight: 0.4
                },
                {
                    name: "IATA",
                    weight: 0.1
                },
                {
                    name: "name",
                    weight: 0.2
                },
                {
                    name: "city",
                    weight: 0.3
                }
            ]
        }
    };


    AirportInput("reservation_data_flightArrivalAirport", airportOptions);
    AirportInput("reservation_data_flightDepartureAirport", airportOptions);


    $('[data-action="next"]').on('click', function(event) {
        event.preventDefault();
        const $activeElement = $("#myTabContent div.active");
        const $activeTabLink = $("#myTabs li button.active");
        console.info($activeElement);
        const $tabs = $('#myTabContent > div');
        const totalTabs = $tabs.length;
        let tabIndex = $activeElement.index();
        console.info('initial Tab Index', tabIndex);
        tabIndex == totalTabs ? tabIndex == totalTabs : tabIndex++;
        console.info('totalTabs', totalTabs, 'tabIndex', tabIndex);
        $activeElement.removeClass('active');
        $activeTabLink.removeClass('active');
        if (tabIndex + 1 == totalTabs) {
            console.info('tabIndex == totalTabs')
            $("#myTabContent > div").last().addClass('active');
            $("#myTabs > li").last().find('button').addClass('active');
            $('[data-action="previous"]').show();
            $(event.currentTarget).hide();
            return;
        }
        if (tabIndex <= totalTabs) {
            $activeElement.next().addClass('active');
            $activeTabLink.parent('li').next().find('button').addClass('active');
            $('[data-action="previous"]').show();
        }
    });
    $('[data-action="previous"]').on('click', function(event) {
        event.preventDefault();
        const $activeElement = $("#myTabContent div.active");
        console.info($activeElement);
        const $activeTabLink = $("#myTabs li button.active");
        let tabIndex = $activeElement.index();
        console.info('tabIndex', tabIndex);
        tabIndex == 0 ? tabIndex == 0 : tabIndex--;
        console.info('tabIndex apres', tabIndex);
        $activeElement.removeClass('active');
        $activeTabLink.removeClass('active');
        if (tabIndex == 0) {
            console.info('tabIndex == 0')
            $("#myTabContent > div").first().addClass('active');
            $("#myTabs > li").first().find('button').addClass('active');
            $('[data-action="next"]').show();
            $(event.currentTarget).hide();
            return;
        }
        if (tabIndex > 0) {
            $activeElement.prev().addClass('active');
            $activeTabLink.parent('li').prev().find('button').addClass('active');
            $('[data-action="next"]').show();
        }
    });
});