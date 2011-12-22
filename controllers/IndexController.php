<?php
class ItemOrder_IndexController extends Omeka_Controller_Action
{
    public function indexAction()
    {
        // Set the collection.
        $collection = $this->getDb()->getTable('Collection')->find($this->_getParam('collection_id'));
        
        // Refresh the collection items order and set the ordered items.
        $itemOrderTable = $this->getDb()->getTable('ItemOrder');
        $itemOrderTable->refreshItemOrder($this->_getParam('collection_id'));
        $items = $itemOrderTable->fetchOrderedItems($this->_getParam('collection_id'));
        
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
    
    /**
     * Reset the order.
     */
    public function resetOrderAction()
    {
        $this->getDb()->getTable('ItemOrder')->resetOrder($this->_getParam('collection_id'));
        $this->flashSuccess('The items have been reset to their default order.');
        $this->_helper->redirector->gotoUrl('/collections/show/' . $this->_getParam('collection_id'));
    }
}
