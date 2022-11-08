import $, { holdReady } from 'jquery';

Dropzone.autoDiscover = false;

$(document).ready(function() {
    initializeDropzone();
});

function initializeDropzone() {
    /* var formElement = document.querySelector('.js-document-dropzone');
    if (!formElement) {
        return;
    }
    var dropzone = new Dropzone(formElement, {
        paramName: 'document'
    }); */

    $('.js-document-dropzone').each(function() {
        var idLabel = $(this).attr('id').split('_');
        var paramName = idLabel[0];
        var dropzone = new Dropzone(this, {
            paramName: paramName
        });
    })
}