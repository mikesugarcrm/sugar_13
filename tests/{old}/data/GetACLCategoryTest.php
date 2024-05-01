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

class GetACLCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @ticket 39846
     */
    public function testGetACLCategoryWhenACLCategoryIsDefined()
    {
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->acl_category = 'Bar';

        $this->assertEquals(
            'Bar',
            $bean->getACLCategory()
        );
    }

    /**
     * @ticket 39846
     */
    public function testGetACLCategoryWhenACLCategoryIsNotDefined()
    {
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';

        $this->assertEquals(
            'Foo',
            $bean->getACLCategory()
        );
    }
}
