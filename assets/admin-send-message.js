/**
 * .addEntry('admin-send-message', './assets/admin-send-message.js')
 * templates\admin\reservation\index.html.twig
 */
import adminMessagesApp from './adminMessagesApp.js';

$(document).ready(() => {
    let $wrapper = $('#js-admin-wrapper');

    var AdminMessagesApp = new adminMessagesApp($wrapper);
})