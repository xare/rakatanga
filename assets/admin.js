import './styles/admin.scss';
import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import "bootstrap-datepicker";
import 'dropzone';


import AdminTemplate from './admin-template.js';
/************************ */
/* DROPZONE               */
/************************ */

Dropzone.autoDiscover = false;


/************************* */
/* DOCUMENT READY          */
/************************* */

$(document).ready(function() {

    AdminTemplate.initialize();
    /******************************* */
    /*  COLLECTION TYPE MANAGEMENT   */
    /******************************* */
    // Get the collection Holder
    $collectionHolder = $('#collection_list');
    //append the add new item to the collectionHolder
    $collectionHolder.append($addNewItem);

    $collectionHolder.data('index', $collectionHolder.find('.card').length);
    // add remove button to existing items
    $collectionHolder.find('.card').each(function(item) {
            addRemoveButton($(this));
        })
        //Handle the click event for AddNewItem

    $addNewItem.click(function(e) {
        e.preventDefault();
        //create a new form and append it to the collection holder
        addNewForm();
    });

    /************************ */
    /*  DATEPICKER MANAGEMENT */
    /************************ */
    $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd'
    });

    /*********************** */
    /* DROPZONE              */
    /*********************** */
    itemsList.initialize($('.js-index-container'));
    initializeDropzone(itemsList);

    /*  $('.sortable').sortable(); */

});

/************************************* */
/* FUNCTIONS COLLECTIONTYPE            */
/************************************* */

var $collectionHolder;

var $addNewItem = $('<a href="#" class="btn btn-info">Add new Item</a>');

//Add new Itemms
function addNewForm() {
    //create the form
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    //create the form
    var newForm = prototype;

    newForm = newForm.replace(/__name__/g, index);

    $collectionHolder.data('index', index + 1);
    // Create the card
    var $card = $('<div class="card card-warning"><div class="card-header"></div></div>');

    var $cardBody = $('<div class="card-body"></div>').append(newForm);

    $card.append($cardBody);
    // initialize new DateTime fields
    var $newRow = $card.children().last();
    //
    $newRow.children('.datepicker').each(function() {

        document.write("Hello");
        /* initializeDatePicker($(this)); */
    });
    //append the removeButton to the new panel
    addRemoveButton($card);

    //append the new card to the collectionHolder
    $addNewItem.before($card);
}

function addRemoveButton($card) {
    //create remove button
    var $removeButton = $('<a href="#" class="btn btn-danger">Remove</a>');

    var $cardFooter = $('<div class="card-footer"></div>').append($removeButton);
    // Handle the click event of the remove button

    $removeButton.click(function(e) {
        e.preventDefault();
        $(e.target).parents('.card').slideUp(500, function() {
            $(this).remove();
        });
    });

    //append the footer to the card
    $card.append($cardFooter);
}

/******************************** */
/* FUNCTIONS DATEPICKER           */
/******************************** */
function initializeDatePicker($row) {
    require("bootstrap-datepicker");
    $row.datepicker({
        format: 'yyyy-mm-dd 00:00:00'
    });
}

/***************************************** */
/* FUNCTIONS INDEX MANAGEMENT              */
/***************************************** */
var itemsList = {
    initialize: function($wrapper) {
        this.$wrapper = $wrapper;
        this.items = [];
        this.$totalItemCount = this.$wrapper.find('.count').children('#totalItemCount');
        this.$navigation = this.$wrapper.find('.navigation');
        this.$paginationLink = this.$wrapper.find('.page-link');
        this.$itemsContainer = this.$wrapper.find('.js-items-list');
        this.itemsPerPage = 10;
        this.pageItems = [];
        this.offset = 0;
        this.count = 0;
        this.presentPage = 0;
        this.properties = [];
        this.render;
        this.$wrapper.on(
            'click',
            '.page-link',
            this.resetIndex.bind(this)
        );
        this.$wrapper.on(
            'click',
            '.sort-column',
            this.sortColumn.bind(this)
        );
        this.$wrapper.on(
            'click',
            '[name="dropzonetype"]',
            this.toggleDropzoneTypes.bind(this)
        );
        this.$wrapper.on(
            'click',
            '.js-delete-item',
            this.handleItemDelete.bind(this)
        );
        this.$wrapper.on(
            'input',
            '.js-search-items',
            this.handleSearch.bind(this)
        )
        if (this.$wrapper.data('dynamic-table') == 'no') { throw ''; }

        this.loadItems();

    },
    loadItems: function() {
        $.ajax({
            url: this.$wrapper.data('url')
        }).then(data => {
            //
            this.items = data.items;
            this.count = data.count;
            this.presentPage = data.presentPage;
            this.itemsPerPage = data.itemsPerPage;
            //this.$totalItemCount.append(data.count);
            this.$totalItemCount.html(data.count);
            this.changeUrl(data.presentPage);
            this.properties = data.properties;
            this.$navigation.html(AdminTemplate.renderPagination(data.count, data.presentPage));
            this.$itemsContainer.html(AdminTemplate.renderTable(data.items, this.offset, this.itemsPerPage));
        });
    },
    toggleDropzoneTypes: function(event) {
        event.preventDefault();
        var type = $(event.currentTarget).val();
        if (type == 'travel') {
            $("[name='category']").hide();
            $("[name='travel']").show();
        }
        if (type == 'country') {
            $("[name='travel']").hide();
            $("[name='category']").show();
        }
        //
    },
    resetIndex: function(event) {
        event.preventDefault();
        this.presentPage = $(event.currentTarget).text();
        this.offset = ((this.presentPage - 1) * this.itemsPerPage) + 1;
        //
        this.$navigation.html(AdminTemplate.renderPagination(this.count, this.presentPage));
        this.$itemsContainer.html(AdminTemplate.renderTable(this.items, this.offset, this.itemsPerPage));
    },
    addItem: function(item) {
        var previousCount = this.$totalItemCount.text();
        previousCount = parseInt(previousCount, 10);
        var newCount = parseInt(previousCount + 1, 10);
        this.$totalItemCount.text(newCount);
        if (previousCount < 10) {
            this.items.push(item);
            this.resetIndex();
        }
    },

    changeUrl: function(number) {
        this.$itemsContainer.attr({
            'data-url': '/crud/media/?page=' + number
        });
    },

    sortColumn: function(event) {
        event.preventDefault();
        var parameter = $(event.currentTarget).text();
        var direction = $(event.currentTarget).data('direction');
        //
        this.items.sort(function(a, b) {
            var x = a[parameter];
            var y = b[parameter];

            if (isNaN(x) && isNaN(y)) {

                x.toLowerCase();
                y.toLowerCase();
                if (direction == "sort") {

                    direction = "reverse";

                    if (x == null && y != null) {
                        console.log('first null')
                        return -1;
                    } else if (x < y) {
                        return -1;
                    } else if (x > y) {
                        return 1;
                    }
                } else {

                    direction = "sort";
                    if (x == null && y != null) {
                        console.log('null')
                        return 1;
                    } else if (x < y) {
                        return 1;
                    } else if (x > y) {
                        return -1;
                    }
                }
                return 0;
            } else {
                if (direction == "sort") {
                    return x - y;
                } else {
                    return y - x;
                }
                return 0;
            }

        });
        this.$itemsContainer.html(AdminTemplate.renderTable(this.items, this.offset, this.itemsPerPage, direction));
    },

    /* Function in charge of deleting an item */
    handleItemDelete: function(e) {
        e.preventDefault();
        var $link = $(e.currentTarget);
        $link.removeClass('btn-danger').addClass('text-danger btn-light');
        $link.find('.fa')
            .removeClass('fa-trash')
            .addClass('fa-spinner')
            .addClass('fa-spin');

        var deleteUrl = $link.data('url');
        var row = $link.closest('tr');
        var $row = $(row);
        var self = this;
        $.ajax({
            url: deleteUrl,
            method: 'POST',
            success: function() {
                $row.fadeOut('normal', function() {
                    $(this).remove();
                    self.loadItems(this.$wrapper);
                });

            }
        })
    },

    handleSearch: function(e) {
        e.preventDefault;
        var inputSearch = $(e.currentTarget);
        var searchTerm = inputSearch.val();
        if (searchTerm.length > 3) {

            $.ajax({
                url: "crud/" + entity + "/search",
                method: "POST",
                success: function() {

                }
            });
        }

    },
    /* function to update the total item count shown on top */
    updateTotalItems: function() {
        var totalItemCount = 0;
        this.$wrapper.find('tbody tr').each(function() {
            totalItemCount++;
            document.getElementById('totalItemCount').innerText = totalItemCount;
        });
    }


}




/**
 * @returns @param {ItemsList} itemsList
 */

function initializeDropzone(itemsList) {
    var formElement = document.querySelector('.js-media-dropzone');
    if (!formElement) {
        return;
    }
    var dropzone = new Dropzone(formElement, {
        paramName: 'files',
        init: function() {
            this.on('success', function(file, data) {
                itemsList.addItem(data);
            })
            this.on('error', function(file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail);
                }
            })
        }
    })
}