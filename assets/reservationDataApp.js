class reservationDataApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.counter = $('#js-progress').attr('data-counter');
        this.totalInputs = this.$wrapper.find('input');
        this.totalInputsIndex = this.totalInputs.map((index, element) => {
            $(element).attr('data-index', index);
        });
        let input = this.$wrapper.find('input:not(".datepicker")');
        let inputDatepicker = this.$wrapper.find('input.datepicker');
        let notInputDatepicker = this.$wrapper.find('input:not(".datepicker")');
        this.filledElements = this.$wrapper.find('[data-filled="data-filled"]');
        console.info(this.filledElements);
        console.info(this.filledElements.length);
        this.$wrapper.on(
            'blur',
            'input',
            this.handleBlur.bind(this)
        );


        this.$elementListContainer = this.$wrapper.find('#js-verification-list');
        this.elements = this.$elementListContainer.find('li');

    }

    handleBlur(event) {
        event.preventDefault();
        console.info("new value for", event.target.id, event.target.value);
        let self = this;
        let thisElement = event.currentTarget;
        let filledElements = [];
        console.info(this.filledElements);
        console.info($(thisElement).val());
        if ($(thisElement).val() != '') {
            $(thisElement).attr('data-filled', 'data-filled');
            filledElements = [thisElement, ...this.filledElements];
        }
        console.info(this.filledElements);
        console.info(filledElements);
        console.info(filledElements.length);
        let type = $(thisElement).attr('data-type');

        if (undefined !== type) {
            $('#js-progress').attr('data-counter', parseFloat(filledElements.length) + parseFloat(1));
            $(thisElement).addClass('checked-control');
            self._redrawProgressBar();
            this._checkList(type);
        } else {
            $('#js-progress').attr('data-counter', parseFloat(filledElements.length) + parseFloat(1));
            self._redrawProgressBar();
            this._checkList(type);
        }
    }
    _checkList(type) {
        const self = this;
        console.info(this.elements);
        this.elements.map((index, element) => {
            if (type == $(element).attr('data-type')) {;
                $(element).attr('data-checked', true);
                $(element).removeClass('bg-warning');
                $(element).addClass('list-success');
                $(element).find('i').removeClass('fa-exclamation-triangle').addClass('fa-check-circle').css('color', '#155724');
            }
        })
    }
    _said_validate(value, type) {
        /* if (type == "passportNo") {
            var regsaid = /[a-zA-Z]{1,3}[0-9]{6,7}/;
        } else */
        if (type == "flightnumber") {
            var regsaid = /([A-Z]{3}|[A-Z\d]{2})(?:\s?)(\d{1,4})/;
        }
        /* else if (
                   type == "passportIssueDate" ||
                   type == "passportExpirationDate" ||
                   type == "passportIssueDate") {
                   var regsaid = /(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-\d{4}/;
               } */
        else {
            var regsaid = /[\s\S]*/;
        }
        return regsaid.test(value);
    }

    _redrawProgressBar() {
        let total = this.totalInputs.length;
        let filledElements = $('[data-filled="data-filled"]');
        let progressBar = $('#js-progress .progress .progress-bar');
        let progress = (eval(filledElements.length) / eval(total)) * 100;
        $('#js-progress .progress .progress-bar').css('width', progress + '%');
        if (progress >= 0 && progress < 25) {
            progressBar.addClass('bg-danger');
        } else if (progress >= 25 && progress < 50) {
            progressBar.removeClass('bg-danger').addClass('bg-warning');
        } else if (progress >= 50 && progress < 75) {
            progressBar.removeClass('bg-warning').addClass('bg-info');
        } else if (progress >= 75 && progress <= 100) {
            progressBar.removeClass('bg-info').addClass('bg-success');
        }
    }

    /* render() {;
        this.$elementListContainer.html('Hello');
    } */
}

export default reservationDataApp;