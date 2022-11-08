import Dropzone from 'dropzone';
import infodocsApp from './infodocsApp.js';

Dropzone.autoDiscover = false;

$(document).ready(function() {
    const $wrapper = $('.js-infodocs-wrapper');
    let infodocsObject = new infodocsApp($wrapper);
    initializeDropzone();

});

function initializeDropzone() {
    var Element = document.querySelector('.js-infodoc-dropzone');
    if (!Element) {
        return;
    }
    var dropzone = new Dropzone(Element, {
        paramName: 'files',
        init: function() {
            /* this.on('sending', function(file, xhr, data) {
                data.append('dropzone_upload', "yes");
                data.append('type', type);
                data.append(type, id);
            }); */
            this.on('success', function(file, data) {
                $('.js-list-infodocs').empty();
                $('.js-list-infodocs').html(data);
            });

            this.on('error', function(file, data) {
                console.error(data);
                if (data.detail) {
                    this.emit('error', file, data.detail);
                    $('.js-list-infodocs').empty();
                    $('.js-list-infodocs').html(data.detail);
                }
            });
        }
    });
}