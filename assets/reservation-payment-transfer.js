import Dropzone from 'dropzone';


let transferDropzone = new Dropzone("#js-payment-dropzone", {
    maxFiles: 1,
    init: function() {
        console.info($('#js-payment-ammount').val());
        this.on('sending', (file, xhr, data) => {
            data.append('ammount', $('#js-payment-ammount').val());
        });
        this.on('success', (file, response) => {
            $('#js-payment-dropzone').html(response.dropHtml);
        });
    }
});