<?php
require_once 'Omeka/Plugin/Abstract.php';

class ItemOrderPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install', 
        'uninstall', 
        'after_save_item', 
        'after_delete_item', 
        'item_browse_sql', 
        'admin_append_to_collections_show_primary', 
    );
    
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS {$this->_db->ItemOrder} (
            id int(10) unsigned NOT NULL AUTO_INCREMENT,
            collection_id int(10) unsigned NOT NULL,
            item_id int(10) unsigned NOT NULL,
            `order` int(10) unsigned NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $this->_db->query($sql);
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $sql = "DROP TABLE IF EXISTS {$this->_db->ItemOrder}";
        $this->_db->query($sql);
    }
    
    /**
     * Delete the item order if the collection ID has changed.
     */
     public function hookAfterSaveItem($item)
     {
        if ($item->collection_id) {
            $sql = "
            DELETE FROM {$this->_db->ItemOrder} 
            WHERE collection_id != ? 
            AND item_id = ?";
            $this->_db->query($sql, array($item->collection_id, $item->id));
        } else {
            $this->hookAfterDeleteItem($item);
        }
     }
     
     /**
      * Delete the item order if the item was deleted.
      */
     public function hookAfterDeleteItem($item)
     {
        $sql = "
        DELETE FROM {$this->_db->ItemOrder} 
        WHERE item_id = ?";
        $this->_db->query($sql, $item->id);
    }
    
    /**
     * Order the items while browsing by collection.
     */
    public function hookItemBrowseSql($select, $params)
    {
        // Do not filter if not browsing by collection.
        if (!isset($params['collection'])) {
            return;
        }
        
        // Do not filter if sorting by browse table header.
        if (isset($params['sort_field'])) {
            return;
        }
        
        // Order the collection items by 1) whether an item order exists, 2) the 
        // item order, 3) the item ID.
        $select->joinLeft(array('io' => $this->_db->ItemOrder), 'i.id = io.item_id', array())
               ->reset('order')
               ->order('ISNULL(io.order), io.order ASC, i.id DESC');
    }
    
    public function hookAdminAppendToCollectionsShowPrimary($collection)
    {
?>
<h2>Item Order</h2>
<p><a href="<?php echo uri('item-order', array('collection_id' => $collection->id)); ?>">Order items in this collection.</a></p>
<form action="<?php echo uri('item-order/index/reset-order', array('collection_id' => $collection->id)); ?>" method="post">
    <input type="submit" name="item_order_reset" value="Reset items to their default order" style="float: none; margin: 0;" />
</form>
<?php
    }
}
