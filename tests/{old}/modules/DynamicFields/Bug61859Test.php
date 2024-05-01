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

class Bug61859Test extends TestCase
{
    private $dynamicFields;

    /**
     * @group 61859
     */
    public function testFieldExists()
    {
        $this->assertFalse($this->dynamicFields->fieldExists('contact'));
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('dictionary');

        $leadBean = $bean = BeanFactory::newBean('Leads');
        $this->dynamicFields = new DynamicField('Leads');
        $this->dynamicFields->setup($leadBean);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }
}
