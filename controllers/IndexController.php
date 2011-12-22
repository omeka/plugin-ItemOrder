<?php
class ItemOrder_IndexController extends Omeka_Controller_Action
{
    public function indexAction()
    {
        $itemOrderTable = $this->getDb()->getTable('ItemOrder');
        
        if ($this->_getParam('order_reset')) {
            $itemOrderTable->resetOrder($this->_getParam('collection_id'));
        }
        
        // Set the collection.
        $collection = $this->getDb()->getTable('Collection')->find($this->_getParam('collection_id'));
        
        // Refresh the collection items order and set the ordered items.
        $itemOrderTable->refreshItemOrder($this->_getParam('collection_id'));
        $items = $this->getDb()->getTable('ItemOrder')->fetchOrderedItems($this->_getParam('collection_id'));
        
        $this->view->assign('collection', $collection);
        $this->view->assign('items', $items);
    }
    
    /**
     * Order the items.
     */
    public function updateOrderAction()
    {
        // Allow only AJAX requests.
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->redirector->gotoUrl('/');
        }
        
        // Update the item orders.
        $this->getDb()->getTable('ItemOrder')->updateOrder($this->_getParam('collection_id'), $this->_getParam('items'));
        $this->_helper->json(true);
    }
}
