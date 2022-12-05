/**
 * .addEntry('admin-inscriptions', './assets/admin-inscriptions.js')
 * templates\admin\inscriptions\index.html.twig
 */

import adminInscriptionsApp from './adminInscriptionsApp.js';

$(document).ready(() => {
    const $wrapper = $('[data-container = "table"]');
    let adminIncriptionsObject = new adminInscriptionsApp($wrapper);
});