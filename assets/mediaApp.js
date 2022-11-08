const routes = require('../public/build/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js';
import Swal from 'sweetalert2';

Routing.setRoutingData(routes);

class mediaApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;

        //upload file

        // remove media
        $wrapper.on(
            'click',
            '.js-remove-media',
            this.removeMedia.bind(this)
        );
        // make main media
        $wrapper.on(
            'click',
            '.js-main-media',
            this.makeMainMedia.bind(this)
        );

        // make media part of gallery
        $wrapper.on(
            'click',
            '.js-gallerify-media',
            this.gallerifyMedia.bind(this)
        );

        $wrapper.on(
            'click',
            '.js-add-video',
            this.addVideo.bind(this)
        )
        $wrapper.on(
            'click',
            '#js-open-video-form',
            this.openVideoForm.bind(this)
        )

    }

    makeMainMedia(event) {
        event.preventDefault;
        const mediaId = $(event.currentTarget).data('media-id');
        const entityId = $(event.currentTarget).closest('#js-media-modal-blocks-container').data('entity-id');
        const entityType = $(event.currentTarget).closest('#js-media-modal-blocks-container').data('entity-type');
        console.log('entityId', entityId);
        const $mediaWrappers = this.$wrapper.find('.media-wrapper');

        console.info($mediaWrappers);

        $mediaWrappers.each((index, element) => {
            $(element).is('.selected') ? $(element).removeClass('selected') : '';
        });
        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('assign_main_photo_entity', { 'id': mediaId }),
                    type: "POST",
                    data: { 'entityId': entityId, 'entityType': entityType }
                });
                console.info($(event.currentTarget).closest('.media-wrapper'));
                $(event.currentTarget).addClass('selectedMedia');
                $(event.currentTarget).closest('.media-wrapper').addClass('selected');
                console.info(response);
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })();
    }
    gallerifyMedia(event) {
        event.preventDefault;
        console.info('gallerifyMedia');
        let mediaId = $(event.currentTarget).data('media-id');
        let entityId = this.$wrapper.data('entity-id');
        let data = { 'mediaId': mediaId, 'entityId': entityId };
        if (!$(event.currentTarget).hasClass('selectedGallery')) {
            (async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('gallerify_media'),
                        data,
                        type: "POST"
                    });
                    $(event.currentTarget).addClass('selectedGallery');
                    $(event.currentTarget).closest('.media-wrapper').addClass('selectedGallery');
                    console.info(response);
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            })();
        } else {
            (async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('ungallerify_media'),
                        data,
                        type: 'POST'
                    });
                    $(event.currentTarget).removeClass('selectedGallery')
                    $(event.currentTarget).closest('.media-wrapper').removeClass('selectedGallery');
                    console.info(response);
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            })();
        }
    }

    removeMedia(event) {
        event.preventDefault();
        console.info('inside removeMedia');
        console.info(event.currentTarget);
        let self = this;
        console.log(this);
        console.log('wrapper', this.$wrapper);
        let mediaId = $(event.currentTarget).data('media-id');
        let entityId = $('#js-media-modal-blocks-container').data('entity-id');
        let entityType = $('#js-media-modal-blocks-container').data('entity-type');
        let data = { mediaId, entityId, entityType };
        console.info(data);

        (async() => {
            try {
                const response = await $.ajax({
                    url: Routing.generate('remove-media'),
                    type: 'POST',
                    data
                });
                console.info(response);
                $(event.currentTarget).parents('.media-wrapper').fadeOut(1000);
            } catch (jqXHR) {
                console.error(jqXHR);
            }
        })();
    }

    openVideoForm(event) {
        event.preventDefault();
        //start self executing function
        let entityId = $('#js-media-modal-blocks-container').data('entity-id');
        let entityType = $('#js-media-modal-blocks-container').data('entity-type');
        (
            async() => {
                try {
                    const response = await $.ajax({
                        url: Routing.generate('open-video-form'),
                        type: "GET"
                    });
                    console.info(response);
                    Swal.fire({
                        title: "Introduce una url de youtube",
                        html: response.html,
                        showLoaderOnConfirm: true,
                        preConfirm: async() => {
                            try {
                                const response2 = await $.ajax({
                                    url: Routing.generate('addVideo'),
                                    type: "POST",
                                    data: { 'id': this._youtube_parser($('#js-youtube-input').val()) }
                                });
                                return response2;
                            } catch (error) {
                                console.error(error)
                            }
                        }
                    }).then((result) => {
                        console.info(result.value);
                        Swal.fire({
                            title: "Youtube Video thumbnail",
                            imageUrl: result.value.imgSrc,
                            preConfirm: () => {
                                (async() => {
                                    try {
                                        const response3 = await $.ajax({
                                            url: Routing.generate('upload-youtube-thumbnail'),
                                            data: {
                                                ytcode: result.value.message,
                                                entityType,
                                                entityId
                                            },
                                            type: "POST",
                                            datatype: "json",
                                            encode: true
                                        });
                                        console.info("hello");
                                        console.info(response3.html);
                                        $('#js-media-modal-blocks-container').prepend(response3.html);

                                    } catch (error) {
                                        console.error(error);
                                    }
                                })();
                            }
                        });
                    });
                } catch (error) {
                    console.error(error);
                }
            }
        )();
    }
    addVideo(event) {
        event.preventDefault();
        Swal.fire({
            input: 'url',
            inputLabel: 'Youtube address',
            inputPlaceholder: 'Enter the URL',
            html: '<div class="js-show-video-thumbnail"> <i class="fas fa-cloud-download-alt" > </i></div>',
            showCancelButton: true,
            preConfirm: (url) => {
                let parsed_url = this._youtube_parser(url);
                /* const data = new FormData();
                data.append('url', url); */
                let data = {
                    url: parsed_url
                };
                return fetch(Routing.generate('addVideo'), {
                        method: 'POST',
                        body: JSON.stringify(data),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText)
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        )
                    })
            }
        }).then((result) => {
            console.log(result.value.url);
            async() => {
                try {
                    const response3 = await $.ajax({
                        url: Routing.generate('upload-youtube-thumbnail'),
                        type: "POST",
                        datatype: "json",
                        encode: true
                    });
                    console.info(response3);
                } catch (jqXHR) {
                    console.error(jqXHR);
                }
            }
        });

        /* if (url) {
            Swal.fire(`Entered URL: ${url}`);
        } */

    }
    _youtube_parser(url) {
        url = url.split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
        return (url[2] !== undefined) ? url[2].split(/[^0-9a-z_\-]/i)[0] : url[0];
    }
}

export default mediaApp;