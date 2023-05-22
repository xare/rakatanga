const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';

Routing.setRoutingData(routes);

$(document).ready(() => {
    const $container = $('.container');
    let adminTool = new adminTools($container);

});

class adminTools {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on(
            'click',
            '[data-action="js-copy-content"]',
            this.copyContent.bind(this)
        );
        this.$wrapper.on(
            'click',
            '[data-action="js-email-url"]',
            this.emailUrl.bind(this)
        )
        this.$wrapper.on(
            'change',
            '[data-action="changePaginationItems"]',
            this.changePaginationItems.bind(this)
        )
    }

    copyContent(event) {
        event.preventDefault();
        // Get the text field
        let copyText = document.querySelector('[data-container="js-copy-from-input"]');
        // Select the text field
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.value);

        // Alert the copied text
        Swal.fire({
            'html': `<p>Hemos copiado este texto en tu portapapeles:  </p><p>${copyText.value}</p>`
        });

    }

    emailUrl(event) {
        event.preventDefault();
        // Get the text field
        let copyText = document.querySelector('[data-container="js-copy-from-input"]');
        // Select the text field
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        let url = copyText.value;
        // Copy the text inside the text field
        navigator.clipboard.writeText(url);
        Swal.fire({
            html: `
                    <p>Añade, aquí, la dirección de email a la que quieres enviar este vínculo:</p>
                    <p>${copyText.value}</p>`,
            input: 'text',
            inputLabel: 'Dirección de correo electrónico',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: async(email) => {
                const response = await $.ajax({
                    url: Routing.generate('admin-send-email'),
                    method: 'POST',
                    data: {
                        email,
                        url: url
                    }
                });
                console.info(response);
                //response.json().then((data) => console.info(data));
            }
        });
    }
    /* changePaginationItems(event){
        event.preventDefault();
        let paginationItems = event.currentTarget.value;
        console.info(paginationItems);
        let entity = event.currentTarget.data('entity');
        console.info(entity);
        (
            async ()=>{
                const response = await $.ajax({
                    method: 'post',
                    url: Routing.generate(`${entity}_index`),
                    data: {paginationItems}
                });
            }
        )();
    } */
}