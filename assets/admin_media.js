 import $ from 'jquery';

 /*******************************************************************/
 /* Creation of mediaApp object                                     */
 /*******************************************************************/
 var mediaApp = {
     /*************************************************************/
     /* $wrapper refers to the id wrapping around the index table */
     /*************************************************************/
     initialize: function($wrapper) {
         this.$wrapper = $wrapper;
         /* On click on the delete button */
         this.$wrapper.on(
                 'click',
                 '.js-delete-item',
                 this.handleItemDelete.bind(this)
             ),
             /* On submit the new item form*/
             this.$wrapper.on(
                 'submit',
                 '.js-new-item-form',
                 this.handleNewFormSubmit.bind(this)
             )
     },

     /* function to update the total item count shown on top */
     updateTotalItems: function() {
         var totalItemCount = 0;
         this.$wrapper.find('tbody tr').each(function() {
             totalItemCount++;
             document.getElementById('totalItemCount').innerText = totalItemCount;
         });
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
         console.log(deleteUrl);
         var $row = $link.closest('tr');
         var self = this;
         $.ajax({
             url: deleteUrl,
             method: 'DELETE',
             success: function() {
                 $row.fadeOut('normal', function() {
                     $(this).remove();
                     self.updateTotalItems();
                     //TODO update the pager
                 });

             }
         })
     },

     /* Function to send a new item.  */
     handleNewFormSubmit: function(e) {
         e.preventDefault();

         var $form = $(e.currentTarget);

         var $tbody = this.$wrapper.find('tbody');
         var self = this;

         $.ajax({
             url: $form.attr('action'),
             //url: $form.data('url'),
             method: 'POST',
             //data: $form.serialize(), //does not work with file inputs
             data: new FormData($form[0]),
             contentType: false,
             processData: false,
             success: function(data) {
                 $tbody.append(data);
                 self.updateTotalItems();
                 // TODO update the pager
             },
             error: function(jqXHR) {
                 $form.closest('.js-new-item-form-wrapper').html(jqXHR.responseText);
             }
         });
     },
     UpdatePagination: function(e) {

     }
 };



 $(document).ready(function() {

     var $wrapper = $('.js-entity-index-table');
     mediaApp.initialize($wrapper);

 });