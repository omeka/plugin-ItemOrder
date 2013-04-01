<?php
class ItemOrder_HooksTest extends ItemOrder_Test_AppTestCase
{
    protected $_isAdminTest = true;

    public function setUp()
    {
        parent::setUp();        
        $this->dbHelper = new Omeka_Test_Helper_Db($this->db->getAdapter(), $this->db->prefix);
    }
    
    public function testAdminCollectionsShow()
    {
        $this->_authenticateUser($this->_getDefaultUser());
        $metadata = array('name' => 'testcollection');
        $collection = insert_collection($metadata);
        $this->dispatch('/collections/show/id/' . $collection->id);
        $this->assertQuery('#item_order_admin_collection_show');
    }
}