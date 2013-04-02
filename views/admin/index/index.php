<?php
$head = array('title' => 'Item Order', 'bodyclass' => 'primary');
echo head($head);
?>
<script>
jQuery(function() {
  jQuery('#sortable').sortable({
    update: function(event, ui) {
      jQuery.post(
        'item-order/index/update-order?collection_id=<?php echo $collection->id; ?>', 
        jQuery('#sortable').sortable('serialize'), 
        function(data) {}
      );
    }
  });
  jQuery('#sortable').disableSelection();
});
</script>
<style>
    #sortable { list-style-type: none; margin: 0; padding: 0; }
    #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em;  background: #FFFFE8;}
    #sortable li span { position: absolute; margin-left: -1.3em; }
    .ui-sortable-helper { background: #FFFFE8; }
</style>
<div id="primary">
<h2>Order Items in Collection "<?php echo html_escape(metadata($collection, array('Dublin Core', 'Title'))); ?>"</h2>
<p>Drag and drop the items below to change their order.</p>
<p>Changes are saved automatically.</p>
<p><a href="<?php echo url('collections/show/' . $collection->id); ?>">Click here</a> 
to return to the collection show page.</p>
<p id="message" style="color: green;"></p>
<ul id="sortable">
    <?php foreach ($items as $item): ?>
    <?php
    $itemObj = get_record_by_id('item', $item['id']);
    $title = strip_formatting(metadata($itemObj, array('Dublin Core', 'Title')));
    $creator = strip_formatting(metadata($itemObj, array('Dublin Core', 'Creator')));    
    $dateAdded = format_date(strtotime($item['added']), Zend_Date::DATETIME_MEDIUM);
    ?>
    <li id="items-<?php echo html_escape($item['id']) ?>" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
    <em><?php echo html_escape($title); ?></em>
    <br/>
    by <?php echo $creator ? html_escape($creator) : '[no creator]'; ?>
    (added <?php echo html_escape($dateAdded); ?>)
    (<a href="<?php echo url('items/show/' . $itemObj->id); ?>" target="_blank">link</a>)</li>
    <?php endforeach; ?>
</ul>
</div>
<?php echo foot(); ?>