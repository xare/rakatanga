import $, { holdReady } from 'jquery';
import Swal from 'sweetalert2';

$(document).ready(function() {
    /*********************** */
    /* Sweet alerts          */
    /*********************** */
    $("[data-action='read-more']").on('click', (event) => {
        event.preventDefault();
        let content = $(event.currentTarget).data('content');
        let title = $(event.currentTarget).data('title') || 'READ MORE';
        Swal.fire({
            'title': title,
            'html': content,
            'customClass': 'swal-wide',
        });
    });
});