const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Dropzone from 'dropzone';
Routing.setRoutingData(routes);
import Swal from 'sweetalert2';
class DocumentsList {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$element = this.$wrapper.find('[data-action="js-document-delete"]');
        this.reservationId = this.$wrapper.data('reservation-id');
        this.travellerId = this.$wrapper.data('traveller-id');
        this.documents = [];

        this.$wrapper.on(
            'click',
            '[data-action="js-document-delete"]',
            this.handleDocumentDelete.bind(this)
        );

    }
    addDocument(document) {
        this.documents.push(document);

    }
    handleDocumentDelete(event) {
        event.preventDefault();
        const self = this;
        const $li = $(event.currentTarget).closest('.list-group-item');
        $li.addClass('disabled');
        const data = $(event.currentTarget).data();
        (async(data) => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('frontend_user_delete_document', {
                        'document': data.documentId
                    }),
                    data,
                    method: 'POST',
                });
                $('[data-container="js-documents-list"]').html(response.listHtml);
                $(`#js-dropzone-${data.type}-container`).html(response.dropHtml);
                self.initializeDropzone(data.type, self.travellerId);
            } catch (jqXHR) {
                console.error(jqXHR)
            }
        })(data);
    }

    initializeDropzone(type, travellerId = null) {
        const self = this;
        $(`#js-${type}-dropzone`).dropzone({
            paramName: 'document',
            /* params: {
                type: type,
                traveller: travellerId
            }, */
            maxFiles: 1,
            init: function() {
                this.on('sending', (file, xhr, data) => {
                    data.append('type', type);
                    if (travellerId != null)
                        data.append('traveller', travellerId);
                });
                this.on('success', (file, response) => {
                    $(`#js-${type}-dropzone`).parent().html(response.dropHtml);
                    $('.js-documents-list').html(response.listHtml);
                });
                this.on("maxfilesexceeded", function(file) {
                    Swal.fire({
                        'title': 'MaxFilesExceeded',
                        'text': 'MaxFilesExceeded'
                    });
                });
                this.on('error', (file, errorMessage) => {
                    Swal.fire({
                        'title': 'Error',
                        'html': errorMessage.detail
                    });
                    if (errorMessage.detail) {
                        this.emit('error', file, errorMessage);
                        setTimeout(function() {
                            window.location.reload();
                            /* $(`#js-${type}-dropzone`).parent().html("error"); */
                        }, 10000);
                    }
                });
            }
        });
    }
}

export default DocumentsList;