import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['mediaSelector', 'select'];
    static values = {
        url: String,
    };
    async selectMedia(event) {
        var urlElements = this.urlValue.split('/', 4).reverse();;;
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

    }
}