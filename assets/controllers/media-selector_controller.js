import { Controller } from 'stimulus';

export default class extends Controller {
    static targets = ['mediaSelector', 'select'];
    static values = {
        url: String,
    };
    async selectMedia(event) {
        var urlElements = this.urlValue.split('/', 4).reverse();
        console.log(urlElements[0]);
        console.log(event.currentTarget.dataset.mediaId);
        this.mediaSelectorTargets.forEach((element) => {
            element.parentNode.parentNode.classList.remove('selected');
            if (element.firstChild.nextSibling === null) {
                element.innerHTML = "<i class='fa fa-star'></i>";
            } else {
                element.firstChild.nextSibling.classList.add('fa', 'fa-star');
            }
        });
        event.currentTarget.parentNode.parentNode.classList.add('selected');
        if (event.currentTarget.firstChild.nextSibling !== null) {
            event.currentTarget.firstChild.nextSibling.classList.remove('fa', 'fa-star');
        } else {
            event.currentTarget.firstChild.innerHtml = "<i class='fa fa-star'></i>";
        }
        const params = new URLSearchParams({
            addPhoto: event.currentTarget.dataset.mediaId,
            preview: 1
        });
        const response = await fetch(`${this.urlValue}?${params.toString()}`);
        console.log(await response.text());

        /* this.selectTarget.options.forEach((element) => {
            if (element.value == event.currentTarget.dataset.mediaId) {
                if (element.selected == true) {
                    element.selected = false
                } else {
                    element.selected = true;
                }
            }
        }); */


    }
}