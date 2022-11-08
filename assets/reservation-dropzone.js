import $, { holdReady } from 'jquery';
import 'dropzone';

/*******************************************************************/
/* Creation of mediaApp object                                     */
/*******************************************************************/
var docsApp = {
    /***********************************************************************/
    /* $wrapper refers to the id wrapping the row containing all elements */
    /**********************************************************************/
    initialize: function($wrapper) {
        this.$wrapper = $wrapper;
        this.$wrapper.on(
            'click',
            '.js-load-dropzone',
            this.loadDropzoneArea.bind(this)
        )
    },
    loadDropzoneArea: function(e) {
        e.preventDefault();
        var $link = $(e.currentTarget);
        var dateId = $link.data('date-id');
        var doctype = $link.data('doctype');
        var url = "/reservation/" + dateId + "/createDropzone/" + doctype;
        $.ajax({
            url: url,
            type: 'get',
            success: function(data) {
                $('.js-dropzone-area').append(data);
            }
        });
    },
}

$(document).ready(function() {
    var $wrapper = $('.js-documents-area');
    docsApp.initialize($wrapper);
});