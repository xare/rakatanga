/**
 * .addEntry('admin-send-message', './assets/admin-send-message.js')
 * templates\admin\reservation\index.html.twig
 */
import adminMessagesApp from './adminMessagesApp.js';

$(document).ready(() => {
    let $wrapper = $('[data-container="js-reservation-admin-index"]');

    var AdminMessagesApp = new adminMessagesApp($wrapper);
})