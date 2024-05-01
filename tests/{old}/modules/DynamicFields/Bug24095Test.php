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

/**
 * @ticket 24095
 */
class Bug24095Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('custom_field', [
            'Accounts',
            [
                'name' => 'foo',
                'type' => 'varchar',
            ],
        ]);

        $GLOBALS['db']->query("INSERT INTO accounts_cstm (id_c,foo_c) VALUES ('12345','67890')");
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '12345'");
        SugarTestHelper::tearDown();
    }

    public function testDynamicFieldsRetrieveWorks()
    {
        $bean = BeanFactory::newBean('Accounts');
        $bean->id = '12345';
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->foo_c, '67890');
    }
}
