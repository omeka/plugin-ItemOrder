<?php
require_once 'ItemOrderTable.php';

class ItemOrder extends Omeka_Record
{
    public $collection_id;
    public $item_id;
    public $order;
}
