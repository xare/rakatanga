import Dropzone from 'dropzone';

/************************ */
/* DROPZONE               */
/************************ */

Dropzone.autoDiscover = false;

$(document).ready(function() {

    initializeDropzone();
});

function initializeDropzone() {
    console.log("Dropzone initialized!");
    var Element = document.querySelector('.js-media-dropzone');
    var type = document.querySelector('#js-media-type').value;
    var id = document.querySelector('#js-media-id').value;
    console.log(Element);
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
            this.on('success', function(file, data) {
                console.log(data);
                $('#js-media-modal-container').prepend(data);
            });

            this.on('error', function(file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail);
                }
            });
        }
    });
}