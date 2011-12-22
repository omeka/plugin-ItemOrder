<?php
$head = array('title' => 'Item Order');
head($head);
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
    #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; }
    #sortable li span { position: absolute; margin-left: -1.3em; }
</style>
<h1><?php echo $head['title']; ?></h1>
<div id="primary">
<h2>Order Items in Collection "<?php echo $collection->name; ?>"</h2>
<form method="post">
    <input type="submit" name="order_reset" value="Reset order" style="float: none; margin: 0;" />
</form>
<p id="message" style="color: green;"></p>
<ul id="sortable">
    <?php foreach ($items as $item): ?>
    <?php $itemObj = get_item_by_id($item['id']); ?>
    <li id="items-<?php echo $item['id'] ?>" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
    <em><?php echo item('Dublin Core', 'Title', null, $itemObj); ?></em>
    by <?php echo item('Dublin Core', 'Creator', null, $itemObj); ?>
    (added <?php echo format_date(strtotime($item['added']), Zend_Date::DATETIME_MEDIUM); ?>)
    (<a href="<?php echo uri('items/show', array('id' => $itemObj->id)); ?>" target="_blank">link</a>)</li>
    <?php endforeach; ?>
</ul>
</div>
<?php foot(); ?>