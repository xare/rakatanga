import $, { holdReady } from 'jquery';
require("bootstrap-datepicker");
$(document).ready(function() {
    $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd'
    });
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd'
    });
});