class reservationCalculator {
    constructor($wrapper) {
        /* GET INITIAL DATA */
        this.$wrapper = $wrapper;
        this.$calculator = $('#js-calculator');
        this.$calculatorTable = this.$calculator.find('table');
        this.$calculatorTbody = this.$calculator.find('tbody');

        this.$pilotesRow = this.$calculatorTbody.find('tr').first();
        this.$pilotesNbLocator = this.$pilotesRow.find('span').first();
        this.$pilotesPriceLocator = this.$pilotesRow.find('span').eq(1);

        this.$passagersRow = this.$calculatorTbody.find('tr').eq(1);
        this.$passagersNbLocator = this.$passagersRow.find('span').first();
        this.$passagersPriceLocator = this.$passagersRow.find('span').eq(1);

        this.$totalPriceLocator = this.$calculator.find('.js-total-price').find('td');
        this.$leftToBePaidLocator = this.$calculator.find('.js-left-payment').find('td');

        this.calculatorData = this.$calculator.data();

        this.costPassagers = this.calculatorData.nbPassagers * this.calculatorData.pricePassagers;


        this.options = [];
        var costOptions = 0;

        this.$calculator.find('.js-option-row').each(function() {
            this.optionAmmount = $(this).attr('data-ammount');
            var optionPrice = $(this).attr('data-value');
            var costOptions = costOptions - (this.optionAmmount * optionPrice);
        });

        this.TotalAmmount = this.costPilotes + this.costPassagers + costOptions;
        this.payment = this.$calculator.data('payment');
        this.leftToBePaid = this.TotalAmmount - this.payment;
        //formatter variable...
        this.formatter = new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR',

            // These options are needed to round to whole numbers if that's what you want.
            minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
            maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
        });

    }

    loadRows(nbPilotes = 0, nbPassagers = 0, options = [], discountType = 'ammount', discount = 0, codepromo = "") {
        //console.info('options within calculator', options);
        if (options.length > 0) {
            this.$calculator.find('.js-reservation-options').empty();
        }
        var self = this;
        let totalPricePilotes = nbPilotes * this.calculatorData.pricePilote;
        let totalPricePassagers = nbPassagers * this.calculatorData.pricePassager;
        let totalPrice = totalPricePilotes + totalPricePassagers;
        this.$pilotesNbLocator.text(nbPilotes);
        this.$pilotesPriceLocator.text(this.formatter.format(totalPricePilotes));
        this.$passagersNbLocator.text(nbPassagers);


        if (options.length > 0) {
            if (typeof options == "string")
                options = JSON.parse(options);

            totalPrice = options.reduce((acc, { ammount, price }) => {
                    return acc + ammount * price;
                },
                totalPrice);
            var html = ''
            html = options.reduce((acc, { id, ammount, title, price }) => {
                if (ammount > 0) {
                    let accumulatedPrice = self.formatter.format(ammount * price);
                    acc += `<tr data-option='${id}'>
                                <td>${ammount} &#10005; ${title}</td>
                                <td align='right'>${accumulatedPrice}</td>
                                </tr>`;
                } else {
                    acc += ''
                }
                return acc
            }, '');

        }
        console.info('totalPrice', totalPrice);
        console.info('discount type', discountType);
        console.info('discount', discount);
        if (discount != 0) {
            if (discountType === "pourcentage") {
                let discountedAmmount = totalPrice * parseInt(discount) / 100;
                totalPrice = totalPrice - discountedAmmount;
                html += `<tr data-codepromo='id' style='color:green; background:#dedede;'>
                        <td>${codepromo} - ${discount} %</td>
                        <td align='right'> - ${discountedAmmount} €</td>
                        </tr>`;
            } else {
                totalPrice = totalPrice - discount;
                html += `<tr data-codepromo='id' style='color:green; background:#dedede;'>
                        <td>${codepromo}</td>
                        <td align='right'>${discount} €</td>
                        </tr>`;
            }
        }
        console.info('totalPrice', totalPrice);

        let leftToBePaid = totalPrice - this.payment;

        this.$passagersPriceLocator.text(this.formatter.format(totalPricePassagers));
        this.$calculatorTable.find('.js-reservation-options').append(html);
        this.$totalPriceLocator.text(this.formatter.format(totalPrice));
        this.$leftToBePaidLocator.text(this.formatter.format(leftToBePaid));
    }

    updateTotal() {
        var total = 0;
        this.$calculator.find('.js-calculation-row').each(function(index, element) {
            total = total + $(element).attr('data-ammount') * $(element).attr('data-value');
        });

        return this.formatter.format(total);
    }
    updateNb(operation, nb) {
        var operationArray = operation.split("-");
        var $row = this.$calculator.find('#js-' + operationArray[1] + 's-row');
        if (operationArray[0] == "remove") {
            nb--;
        }
        if (operationArray[0] == "add") {
            nb++;
        }

        var price = $row.attr('data-value');
        $row.find('#js-total-' + operationArray[1] + '').html(nb);
        $row.find('#js-total-price-' + operationArray[1] + '').html(this.formatter.format(nb * price));
        $row.attr('data-nb', nb);
        this.$calculator.find('#js-total-price-reservation').html(this.updateTotal.bind(this));
        if (nb == 0) {
            $row.remove();
        }
        return nb;
    }
    updateNbFromSelect(type, nb) {
        this.$calculator.attr('data-nb-' + type, nb);
        this.loadRows(nb, 0, '')
    }
    updateOption(operation, ammount, id) {
        console.log('update option')
        var $row = this.$calculator.find('#js-option-row-' + id);
        var price = $row.attr('data-value');
        var operationArray = operation.split("-");
        if (operationArray[0] == "remove") {
            ammount--;
        }
        if (operationArray[0] == "add") {
            ammount++;
        }

        $row.find('.js-option-ammount').html(ammount);
        $row.find('.js-total-option').html(this.formatter.format(ammount * price));
        $row.attr('data-nb', ammount);
        this.$calculator.find('#js-total-price-reservation').html(this.updateTotal.bind(this));
        if (ammount == 0) {
            $row.remove();
        }
        return ammount;
    }

    addOption(optionId, price, optionTitle) {
        var rowHtml = `
                <tr 
                    class="js-calculation-row js-option-row" 
                    id="js-option-row-${optionId}"
                    data-option-id="${optionId}"
                    data-ammount="1"
                    data-field="option"
                    data-value="${price}"
                    data-option-name="${optionTitle}">
                    <td>
                        <span class="js-option-ammount">1</span>
                        x
                        ${optionTitle}
                    </td>
                    <td class="js-total-option" align="right">
                        ${this.formatter.format(price)}
                    </td>
                </tr>`;
        this.$calculator.find('tbody').append(rowHtml);
        this.$calculator.find('#js-total-price-reservation').html(this.updateTotal.bind(this));
    }

    updateOptionNew(option) {
        //Gather stored options in calculator div data attribute.
        let options = $('#js-calculator').attr('data-options');

        // In case the reservation is NOT initialised
        if (options == "") {
            options = [...[option]];
            // In case the reservation IS initialised    
        } else {
            options = JSON.parse(options);
            let doNotPush = false;
            options.map((optionElement, i) => {
                if (optionElement.id == option.id) {
                    options[i].ammount = option.ammount;
                    doNotPush = true;
                }
            });

            if (doNotPush == false) {
                options = [...options, ...[option]];
                console.table(options);
            }

        }

        const optionsString = '\n' + JSON.stringify(options, '\t', 2);
        $('#js-calculator').attr('data-options', optionsString);
        let discount = this.$calculator.attr('data-discount');
        let discountType = this.$calculator.attr('data-discount-type');
        this.loadRows(
            this.$calculator.attr('data-nb-pilotes'),
            this.$calculator.attr('data-nb-passagers'),
            options,
            discountType,
            discount);
    }

    /* _getPreviousOptions() {
        let reservationId = this.calculatorData.reservation;
        const self = this;
        const url = Routing.generate('get_previous_options', { reservation: reservationId });
        $.ajax({
                url: url,
                type: "GET"
            })
            .then((response) => {
                let html = "";
                response.options.forEach((element) => {
                    html = html + self._optionRow(element.ammount, element.id, element.title, element.price);

                });
                this.$calculator.find('.js-reservation-options').html(html);
            })
            .catch((jqXHR) => {
                console.log(jqXHR)
            });
    } */
    /* _optionRow(ammount, id, title, price) {
        return `
                <tr 
                    class="js-calculation-row js-option-row" 
                    id="js-option-row-${id}"
                    data-option-id="${id}"
                    data-nb="${ammount}"
                    data-field="option"
                    data-value="${price}"
                    data-option-name="${title}">
                    <td>
                        <span class="js-option-ammount">${ammount}</span>
                        x
                        ${title}
                    </td>
                    <td class="js-total-option" align="right">
                        ${this.formatter.format(price)}
                    </td>
                </tr>`;
    } */
}
export default reservationCalculator;