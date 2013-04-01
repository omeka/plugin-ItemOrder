<?php
class Table_ItemOrder_ItemOrder extends Omeka_Db_Table
{
    /**
     * Fetch the items in order for the specified collection.
     * 
     * @param int $collectionId
     * @return array
     */
    public function fetchOrderedItems($collectionId)
    {
        $itemTable = $this->getDb()->Item;
        $itemOrderTable = $this->getDb()->ItemOrder_ItemOrder;
        $sql = "
        SELECT i.* 
        FROM $itemTable AS i 
        LEFT JOIN $itemOrderTable AS io 
        ON i.id = io.item_id 
        WHERE i.collection_id = ? 
        ORDER BY ISNULL(io.`order`), io.`order` ASC, i.id DESC";
        return $this->fetchAll($sql, $collectionId);
    }
    
    /**
     * Refresh the item order for the specified collection.
     * 
     * @param int $collectionId
     */
    public function refreshItemOrder($collectionId)
    {
        $itemTable = $this->getDb()->Item;
        $itemOrderTable = $this->getDb()->ItemOrder_ItemOrder;
        
        // Delete item orders that are no longer assigned to the specified 
        // collection. This is normally done on an item-by-item basis in the 
        // after_item_save hook. This step is included in the event that an item 
        // changes collection without firing the hook.
        $sql = "
        DELETE FROM $itemOrderTable 
        WHERE collection_id = ?
        AND item_id NOT IN (
            SELECT i.id 
            FROM $itemTable AS i 
            WHERE i.collection_id = ?
        )";
        $this->query($sql, array($collectionId, $collectionId));
        
        // Refresh the current item order to start with 1 and be sequentially 
        // unbroken. This step is necessary in the event that items have been 
        // deleted after they were ordered.
        $sql = "SET @order = 0";
        $this->query($sql);
        
        $sql = "
        UPDATE $itemOrderTable 
        SET `order` = (SELECT @order := @order + 1) 
        WHERE collection_id = ? 
        ORDER BY `order` ASC";
        $this->query($sql, $collectionId);
        
        // Get the items in this collection that have not been ordered and order 
        // them, starting at the max order + 1 of the previously ordered items.
        $sql = "
        SET @order = IFNULL(
            (SELECT MAX(`order`) FROM $itemOrderTable WHERE collection_id = ?), 
            0
        )";
        $this->query($sql, $collectionId);
        
        $sql = "
        INSERT INTO $itemOrderTable (collection_id, item_id, `order`) 
        SELECT s.collection_id, s.id, @order := @order + 1  
        FROM (
            SELECT i.collection_id, i.id
            FROM $itemTable AS i 
            LEFT JOIN $itemOrderTable io 
            ON i.id = io.item_id 
            WHERE i.collection_id = ? 
            AND io.id IS NULL 
            ORDER BY i.id DESC
        ) AS s";
        $this->query($sql, $collectionId);
    }
    
    /**
     * Update the item order for the specified collection.
     * 
     * @param int $collectionId
     * @param array $items
     */
    public function updateOrder($collectionId, array $items)
    {
        // Reindex the items array to start at 1.
        $items = array_combine(range(1, count($items)), array_values($items));
        
        $itemOrderTable = $this->getDb()->ItemOrder_ItemOrder;
        foreach ($items as $itemOrder => $itemId) {
            $sql = "
            UPDATE $itemOrderTable 
            SET `order` = ? 
            WHERE collection_id = ? 
            AND item_id = ?";
            $this->query($sql, array($itemOrder, $collectionId, $itemId));
        }
    }
    
    /**
     * Reset the collection item order.
     * 
     * @param int $collectionId
     */
    public function resetOrder($collectionId)
    {
        $itemOrderTable = $this->getDb()->ItemOrder_ItemOrder;
        $sql = "DELETE FROM $itemOrderTable WHERE collection_id = ?";
        $this->query($sql, $collectionId);
    }
}