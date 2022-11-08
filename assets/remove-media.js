import $, { holdReady } from 'jquery';

$(document).ready(function() {
    var $wrapper = $('#js-media-modal-container');
    var baseUrl = $wrapper.attr('data-baseUrl');
    $wrapper.on('click', '.js-remove-media', function(event) {
        event.preventDefault;
        var self = $(this);
        console.log(this);
        console.log(self);
        var mediaId = self.attr('data-media-id');
        var travelId = self.attr('data-travel-id');
        var submitData = { 'mediaId': mediaId, 'travelId': travelId };
        console.log(submitData)
        $.ajax({
            type: 'POST',
            url: baseUrl + '/crud/media/removeMedia',
            data: submitData,
            success: function(result) {
                console.log(result);
                self.parents('.media-wrapper').fadeOut(1000);
            }

        })

    })

});