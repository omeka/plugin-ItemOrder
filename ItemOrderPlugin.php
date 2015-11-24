<?php
/**
* ItemOrderPlugin class - represents the Item Order plugin
*
* @copyright Copyright 2008-2013 Roy Rosenzweig Center for History and New Media
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package ItemOrder
*/

/** Path to plugin directory */
defined('ITEM_ORDER_PLUGIN_DIRECTORY') 
    or define('ITEM_ORDER_PLUGIN_DIRECTORY', dirname(__FILE__));

/**
 * Item Order plugin.
 */
class ItemOrderPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'install', 
        'uninstall',
        'upgrade',
        'define_acl',
        'after_save_item', 
        'after_delete_item', 
        'items_browse_sql',
        'admin_collections_show', 
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'items_browse_default_sort',
    );

    /**
     * @var array Options and their default values.
     */
    protected $_options = array();
    
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS {$this->_db->ItemOrder_ItemOrder} (
            id int(10) unsigned NOT NULL AUTO_INCREMENT,
            collection_id int(10) unsigned NOT NULL,
            item_id int(10) unsigned NOT NULL,
            `order` int(10) unsigned NOT NULL,
            PRIMARY KEY (id),
            KEY `item_id_order` (`item_id`,`order`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $this->_db->query($sql);
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $sql = "DROP TABLE IF EXISTS {$this->_db->ItemOrder_ItemOrder}";
        $this->_db->query($sql);
    }
    
    /**
     * Upgrade the plugin
     */
    public function hookUpgrade($args) 
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        $db = $this->_db;
        
        if (version_compare($oldVersion, '2.0-dev', '<=')) {
            $sql = "ALTER TABLE `{$db->prefix}item_orders` RENAME TO `{$this->_db->ItemOrder_ItemOrder}` ";
            $db->query($sql);
            $sql = "ALTER TABLE `{$this->_db->ItemOrder_ItemOrder}` ADD INDEX `item_id_order` (`item_id`,`order`) ";
            $db->query($sql);            
        }   
    }
    
    /**
     * Define the ACL.
     * 
     * @param array $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl']; // get the Zend_Acl   
        $acl->addResource('ItemOrder_Index');
    }
    
    /**
     * After save item hook.
     *
     * @param array $args
     */
     public function hookAfterSaveItem($args)
     {
        // Delete the item order if the collection ID has changed.
        $item = $args['record'];
        if ($item->collection_id) {
            $sql = "
            DELETE FROM {$this->_db->ItemOrder_ItemOrder} 
            WHERE collection_id != ? 
            AND item_id = ?";
            $this->_db->query($sql, array($item->collection_id, $item->id));
        } else {
            $this->hookAfterDeleteItem($args);
        }
     }
     
     /**
      * After delete item hook.
      *
      * @param array $args
      */
     public function hookAfterDeleteItem($args)
     {
        // Delete the item order if the item was deleted.
        $item = $args['record'];
        $sql = "
        DELETE FROM {$this->_db->ItemOrder_ItemOrder} 
        WHERE item_id = ?";
        $this->_db->query($sql, $item->id);
    }
    
    /**
     * Hooks into items_browse_sql
     *
     * @param array $args
     */
    public function hookItemsBrowseSql($args)
    {
        $db = $this->_db;
        $select = $args['select'];
        $params = $args['params'];
        
        // Order the items while browsing by collection.
        
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
        $select->joinLeft(array('item_order_item_orders' => $db->ItemOrder_ItemOrder), 'items.id = item_order_item_orders.item_id', array())
               ->reset('order')
               ->order(array(
                   'ISNULL(item_order_item_orders.order)',
                   'item_order_item_orders.order ASC',
                   'items.id DESC'
               ));
    }
    
    /**
     * Admin collection show content hook.
     *
     * @param array $args
     */
    public function hookAdminCollectionsShow($args)
    {
        $collection = $args['collection'];
?>
<div id="item_order_admin_collection_show">
<h2>Item Order</h2>
<p><a href="<?php echo url('item-order', array('collection_id' => $collection->id)); ?>">Order items in this collection.</a></p>
<form action="<?php echo url('item-order/index/reset-order', array('collection_id' => $collection->id)); ?>" method="post">
    <input type="submit" name="item_order_reset" value="Reset items to their default order" style="float: none; margin: 0;" />
</form>
</div>
<?php
    }

    /**
     * Ignore the items/browse default sort if a collection was specified in the
     * routing or GET params.
     *
     * @param array $sort
     * @param array $args
     * @return array|null
     */
    public function filterItemsBrowseDefaultSort($sort, $args)
    {
        if (empty($args['params']['collection'])) {
            return $sort;
        } else {
            return null;
        }
    }
}
