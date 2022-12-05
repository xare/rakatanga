/**
 * .addEntry('admin-upload-media', './assets/upload-media.js')
 * templates\admin\travel\edit.html.twig
 */

import Dropzone from 'dropzone';

/************************ */
/* DROPZONE               */
/************************ */

Dropzone.autoDiscover = false;

$(document).ready(function() {

    initializeDropzone();
});

function initializeDropzone() {
    var Element = document.querySelector('.js-media-dropzone');
    var type = document.querySelector('#js-media-type').value;
    var id = document.querySelector('#js-media-id').value;;
    if (!Element) {
        return;
    }
    var dropzone = new Dropzone(Element, {
        paramName: 'files',
        init: function() {
            this.on('sending', function(file, xhr, data) {
                data.append('dropzone_upload', "yes");
                data.append('type', type);
                data.append(type, id);
            });
            this.on('success', function(file, data) {;
                $('#js-media-modal-blocks-container').html(data);
            });

            this.on('error', function(file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail);
                }
            });
        }
    });
}