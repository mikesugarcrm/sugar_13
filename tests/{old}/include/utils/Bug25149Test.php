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
 * @group 25149
 */
class Bug25149Test extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testExportAllProductFields()
    {
        $product = new Product();

        $where = '';
        $query = $product->create_export_query('id', $where);

        $db = DBManagerFactory::getInstance();
        $result = $db->limitQuery($query, 1, 1, true, '');
        $export_fields = $db->getFieldsArray($result, true);

        $query = 'SELECT * FROM ' . $product->table_name;
        $result = $db->limitQuery($query, 1, 1, true, '');
        $table_fields = $db->getFieldsArray($result, true);

        $this->assertGreaterThanOrEqual(count($table_fields), count($export_fields));
    }
}
