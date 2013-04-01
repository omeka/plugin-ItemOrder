<?php
/**
 * ItemOrder_IndexControllerTest - represents the Item Order index controller test.
 *
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package ItemOrder
 */
 
class ItemOrder_IndexControllerTest extends ItemOrder_Test_AppTestCase
{    
    public static function acl()
    {
        return array(
            array(true, 'super', 'ItemOrder_Index', 'index'),
            array(true, 'admin', 'ItemOrder_Index', 'index'),
        );
    }

    /**
     * @dataProvider acl
     */
    public function testAcl($isAllowed, $role, $resource, $privilege)
    {
        $this->assertEquals($isAllowed, $this->acl->isAllowed($role, 
            $resource, $privilege));
    }
}