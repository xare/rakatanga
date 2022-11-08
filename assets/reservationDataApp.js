class reservationDataApp {
    constructor($wrapper) {
        this.$wrapper = $wrapper;
        this.$totalInputs = this.$wrapper.find('input');
        this.counter = $('#js-progress').attr('data-counter');
        this.$totalInputs.map((index, element) => {
            $(element).attr('data-index', index);
        });
        let $input = this.$wrapper.find('input:not(".datepicker")');
        let $inputDatepicker = this.$wrapper.find('input.datepicker');
        this.$wrapper.on(
            'blur',
            'input:not(".datepicker")',
            this.handleBlur.bind(this)
        );
        this.$wrapper.on(
            'blur',
            'input.datepicker',
            this.handleBlur.bind(this)
        );


        this.$elementListContainer = this.$wrapper.find('#js-verification-list');
        this.elements = this.$elementListContainer.find('li');
        //this.render();

    }

    handleBlur(e) {
        e.preventDefault();
        let self = this;
        let $thisElement = $(e.currentTarget);
        let counter = $('#js-progress').attr('data-counter');
        let type = $thisElement.attr('data-type');
        /* let validate = false; */
        if (undefined !== type) {
            /* validate = this._said_validate($thisElement.val(), type); */
            /* if (validate !== false) { */
            $('#js-progress').attr('data-counter', parseFloat(counter) + parseFloat(1));
            $thisElement.addClass('checked-control');
            self._redrawProgressBar();
            this._checkList(type);
            /*}  else {
                $('#js-progress').append(
                    `<strong>${$thisElement.attr('title')}</strong>Error de validación`
                )
            } */
        } else {
            $('#js-progress').attr('data-counter', parseFloat(counter) + parseFloat(1));
            self._redrawProgressBar();
            this._checkList(type);
        }

    }
    _checkList(type) {
        const self = this;
        this.elements.map((index, element) => {
            if (type == $(element).attr('data-type')) {
                console.log($(element));
                $(element).attr('data-checked', true);
                $(element).removeClass('bg-warning').addClass('list-success');
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
        let total = this.$totalInputs.length;
        let counter = $('#js-progress').attr('data-counter');
        let $progressBar = $('#js-progress .progress .progress-bar');
        let progress = (eval(counter) / eval(total)) * 100;
        $('#js-progress .progress .progress-bar').css('width', progress + '%');
        if (progress >= 0 && progress < 25) {
            $progressBar.addClass('bg-danger');
        } else if (progress >= 25 && progress < 50) {
            $progressBar.removeClass('bg-danger').addClass('bg-warning');
        } else if (progress >= 50 && progress < 75) {
            $progressBar.removeClass('bg-warning').addClass('bg-info');
        } else if (progress >= 75 && progress <= 100) {
            $progressBar.removeClass('bg-info').addClass('bg-success');
        }
    }

    render() {
        console.log('render');
        this.$elementListContainer.html('Hello');
        /* const itemsHtml = this.elements.map(item => {
            `hello`
        });
        this.$elementListContainer.html(itemsHtml.join('')); */
    }
}

export default reservationDataApp;