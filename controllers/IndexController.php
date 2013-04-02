<?php
class ItemOrder_IndexController extends Omeka_Controller_AbstractActionController
{
    public function init() 
    {
        $this->_helper->db->setDefaultModelName('ItemOrder_ItemOrder');
    }
    
    public function indexAction()
    {
        $db = $this->_helper->db;
        
        // Set the collection.
        $collection = $db->getTable('Collection')->find($this->_getParam('collection_id'));
        
        // Refresh the collection items order and set the ordered items.
        $itemOrderTable = $db->getTable('ItemOrder_ItemOrder');
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
        $this->_helper->db->getTable('ItemOrder_ItemOrder')->updateOrder($this->_getParam('collection_id'), $this->_getParam('items'));
        $this->_helper->json(true);
    }
    
    /**
     * Reset the order.
     */
    public function resetOrderAction()
    {
        $this->_helper->db->getTable('ItemOrder_ItemOrder')->resetOrder($this->_getParam('collection_id'));
        $this->_helper->flashMessenger('The items have been reset to their default order.', 'success');
        $this->_helper->redirector->gotoUrl('/collections/show/' . $this->_getParam('collection_id'));
    }
}