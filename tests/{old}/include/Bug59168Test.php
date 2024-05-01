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

require_once 'include/utils.php';

class Bug59168Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    /**
     * @dataProvider searchFields
     */

    public function testBug59168($module, $searchFields, $where)
    {
        $exportQuery = create_export_query_relate_link_patch($module, $searchFields, $where);
        $this->assertIsString($exportQuery['join']);
    }

    public function searchFields()
    {
        return [
            ['Contacts', [], ''],
            ['Contacts', ['account_name' => ['value' => 'one']], ''],
        ];
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }
}
