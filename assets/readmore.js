import $, { holdReady } from 'jquery';
import Swal from 'sweetalert2';

$(document).ready(function() {
    /*********************** */
    /* Sweet alerts          */
    /*********************** */
    let $wrapper = $('body');
    this.$wrapper = $wrapper;

    this.$wrapper.on(
        'click',
        "[data-action='read-more']",
        (event) => {
            event.preventDefault();
            let content = $(event.currentTarget).data('content');
            let title = $(event.currentTarget).data('title') || 'READ MORE';
            Swal.fire({
                'title': title,
                'html': content,
                'width': '80%',
                'customClass': 'swal-wide',
                'showCloseButton': true,
            });
        });
});