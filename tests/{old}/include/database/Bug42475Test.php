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
 * @ticket 42475
 */
class Bug42475Test extends TestCase
{
    public function testAuditingCurrency()
    {
        // getDataChanges
        $testBean = new Bug42475TestBean();
        $dataChanges = $testBean->db->getAuditDataChanges($testBean);

        $this->assertEquals(0, count($dataChanges), "New test bean shouldn't have any changes");

        $testBean = new Bug42475TestBean();
        $testBean->test_field = 3829.83862;
        $dataChanges = $testBean->db->getAuditDataChanges($testBean);

        $this->assertEquals(1, count($dataChanges), 'Test bean should have 1 change since we added assigned new value to test_field');
    }
}

class Bug42475TestBean extends SugarBean
{
    public function __construct()
    {
        $this->module_dir = 'Accounts';
        $this->object_name = 'Account';
        parent::__construct();

        // Fake a fetched row
        $this->fetched_row = ['test_field' => 257.8300000001];
        $this->test_field = 257.83;
        $this->field_defs['test_field'] = [
            'type' => 'currency',
        ];
    }

    public function getAuditEnabledFieldDefinitions($includeRelateIdFields = false)
    {
        return ['test_field' => ['type' => 'currency']];
    }
}
