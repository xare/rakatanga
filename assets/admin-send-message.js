import adminMessagesApp from './adminMessagesApp.js';

$(document).ready(() => {
    let $wrapper = $('#js-admin-wrapper');

    var AdminMessagesApp = new adminMessagesApp($wrapper);
})