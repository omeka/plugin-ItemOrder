(function($) {
  $(document).ready(function() {
    var sortableList = $('#sortable');
    var collectionId = sortableList.data('collection-id');
    sortableList.sortable({
      update: function(event, ui) {
        $.post(
          'item-order/index/update-order?collection_id=' + collectionId,
          $('#sortable').sortable('serialize'), 
          function(data) {}
        );
      }
    });
    $('#sortable').disableSelection();
  });
})(jQuery); 