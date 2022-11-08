import $ from 'jquery';
const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';

$(document).ready(() => {
    var $parentSelect = $('[data-action="js-form-parent-select"]');
    var $childSelectContainer = $('[data-container="js-form-select-child"]');
    Routing.setRoutingData(routes);

    $parentSelect.on('change', (e) => {
        $.ajax({
            url: $parentSelect.data('ajax-url'),
            data: {
                ParentValue: $parentSelect.val()
            },
            success: (response) => {
                if (!response) {
                    $childSelectContainer.find('select').remove();
                    $childSelectContainer.addClass('d-none');
                    return;
                }
                // Replace current Field
                $childSelectContainer.html(response).removeClass('d.none');
            }
        })
    });

})