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
 * Bug #43202
 * @description
 *  When filtering search with a 'related' field, it's not possible to export "all" records
 * @author aryamrchik@sugarcrm.com
 * @ticket 43202
 */
class Bug43202Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @group 43202
     */
    public function testExportQuery()
    {
        $focus = BeanFactory::newBean('Accounts');
        //use join name for teams as defined in team security vardefs ('tj')
        $query = $focus->create_export_query('', 'tj.name IS NOT NULL');
        $this->assertTrue($focus->db->validateQuery($query));
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }
}
