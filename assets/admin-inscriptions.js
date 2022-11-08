import adminInscriptionsApp from './adminInscriptionsApp.js';

$(document).ready(() => {
    const $wrapper = $('[data-container = "table"]');
    let adminIncriptionsObject = new adminInscriptionsApp($wrapper);
});