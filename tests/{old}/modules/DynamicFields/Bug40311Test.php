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
class Bug40311Test extends TestCase
{
    /**
     * @var \Account&\PHPUnit\Framework\MockObject\MockObject|mixed
     */
    public $accountMockBean;
    private $tableName;
    private $oldInstalling;

    /**
     * @var SugarTestDatabaseMock
     */
    private $db;

    protected function setUp(): void
    {
        $this->db = SugarTestHelper::setUp('mock_db');

        $this->accountMockBean = $this->getMockBuilder('Account')->setMethods(['hasCustomFields'])->getMock();
        $this->tableName = 'test' . date('YmdHis');
        if (isset($GLOBALS['installing'])) {
            $this->oldInstalling = $GLOBALS['installing'];
        }
        $GLOBALS['installing'] = true;
    }

    protected function tearDown(): void
    {
        if (isset($this->oldInstalling)) {
            $GLOBALS['installing'] = $this->oldInstalling;
        } else {
            unset($GLOBALS['installing']);
        }
        SugarTestHelper::tearDown();
    }

    public function testDynamicFieldsNullWorks()
    {
        $this->db->addQuerySpy(
            'dynamic_field',
            '/' . $this->tableName . '_cstm\.\*/',
            [
                [
                    'id_c' => '12345',
                    'foo_c' => null,
                ],
            ]
        );


        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);
        $bean->expects($this->any())
            ->method('hasCustomFields')
            ->will($this->returnValue(true));
        $bean->table_name = $this->tableName;
        $bean->id = '12345';
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->foo_c, null);
    }
}
