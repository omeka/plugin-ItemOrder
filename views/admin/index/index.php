<?php
    queue_css_file('item-order');
    queue_js_file('item-order');
    $head = array('title' => 'Item Order', 'bodyclass' => 'primary');
    echo head($head);
?>
<div id="primary">
<h2>Order Items in Collection "<?php echo html_escape(metadata($collection, array('Dublin Core', 'Title'))); ?>"</h2>
<p>Drag and drop the items below to change their order.</p>
<p>Changes are saved automatically.</p>
<p><a href="<?php echo url('collections/show/' . $collection->id); ?>">Click here</a> 
to return to the collection show page.</p>
<p id="message" style="color: green;"></p>
<ul id="sortable" class="ui-sortable" data-collection-id="<?php echo $collection->id; ?>">
    <?php foreach ($items as $item): ?>
    <?php
    $itemObj = get_record_by_id('item', $item['id']);
    $title = strip_formatting(metadata($itemObj, array('Dublin Core', 'Title')));
    $creator = strip_formatting(metadata($itemObj, array('Dublin Core', 'Creator')));    
    $dateAdded = format_date(strtotime($item['added']), Zend_Date::DATETIME_MEDIUM);
    ?>
    <li id="items-<?php echo html_escape($item['id']) ?>" class="ui-state-default sortable-item"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
      <span class="item-title"><?php echo $title; ?></span>
      <div class="other-meta">
        <?php if ($creator): ?>
        by <?php echo $creator; ?>
        <?php endif; ?>
        (added <?php echo html_escape($dateAdded); ?>)
        (<a href="<?php echo url('items/show/' . $itemObj->id); ?>" target="_blank">link</a>)
      </div>
    </li>
    <?php endforeach; ?>
</ul>
</div>
<?php echo foot(); ?>