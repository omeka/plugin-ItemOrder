<?php
require_once 'Omeka/Plugin/Abstract.php';

class ItemOrderPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install', 
        'uninstall', 
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
<p><a href="<?php echo uri('item-order', array('collection_id' => $collection->id)); ?>">Order items in this collection.</a></p>
<?php
    }
}
