<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;


class SugarQueryPortalVisibilityTest extends TestCase
{
    public $bean = null;
    public $vis = null;
    public $query = null;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test we call the proper methods
     */
    public function testVisibilityCall()
    {
        $bean = $this->createPartialMock('SugarBean', ['loadVisibility']);

        $vis = $this->getMockBuilder('SupportPortalVisibility')
            ->setMethods(['addVisibilityQuery'])
            ->setConstructorArgs([$bean])
            ->getMock();
        $bean->expects($this->any())->method('loadVisibility')->will($this->returnValue($vis));
        $bean->module_dir = 'test';
        $query = new SugarQuery();
        $vis->expects($this->once())->method('addVisibilityQuery')->with($query)->will($this->returnValue($query));
        $bean->addVisibilityQuery($query);
        unset($vis);
        unset($bean);
        unset($query);
    }
}

class ContactsPortalVisibilityQueryMock extends Contact
{
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }
}

class SupportPortalVisibilityQueryMock extends SupportPortalVisibility
{
    public function getAccountIds()
    {
        return self::$accountIds = ['1', '2', '3', '4'];
    }
}
