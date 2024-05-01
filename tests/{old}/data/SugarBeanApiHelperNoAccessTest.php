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
 * @group ApiTests
 */
class SugarBeanApiHelperNoAccessTest extends TestCase
{
    public $bean;
    public $beanApiHelper;
    public $apiMock;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        // Mocking out SugarBean to avoid having to deal with any dependencies other than those that we need for this test
        $mock = $this->createMock('SugarBean');
        $mock->id = 'SugarBeanApiHelperMockBean-1';
        $mock->favorite = false;
        $mock->module_name = 'Test';
        $mock->module_dir = 'Test';
        $mock->field_defs = [
            'testInt' => [
                'type' => 'int',
            ],
            'testDecimal' => [
                'type' => 'decimal',
            ],
            'testBool' => [
                'type' => 'bool',
            ],
        ];
        $mock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(false));
        $this->bean = $mock;

        $this->apiMock = new SugarBeanApiHelperNoAccessTest_ServiceMockup();
        $this->apiMock->user = $GLOBALS['current_user'];
        $this->beanApiHelper = new SugarBeanApiHelper($this->apiMock);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testNoEmail1FieldAccess()
    {
        $data = [];
        $this->bean->field_defs['email'] = ['type' => 'email'];
        $this->bean->field_defs['email1'] = ['type' => 'varchar'];
        $this->bean->emailAddress = [];
        $this->beanApiHelper->formatForApi($this->bean, ['email', 'email1']);
        $this->assertTrue(!isset($data['email']));
    }

    public function testNoEmail1FieldAccessSave()
    {
        $data = [];
        $this->bean->field_defs['email'] = ['type' => 'email'];
        $this->bean->field_defs['email1'] = ['type' => 'varchar'];
        $this->bean->emailAddress = [];
        $data['email'] = 'test@test.com';
        $data['module'] = 'Test';

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->expectExceptionMessage('Not allowed to edit field email in module: Test');

        $this->beanApiHelper->populateFromApi($this->bean, $data);
    }

    /**
     * @dataProvider providerFunction
     */
    public function testNoFieldAccessWithDiffDefaultSetSave($defaultValue, $fieldValue)
    {
        $data = [];
        $this->bean->field_defs['testInt']['default'] = $defaultValue;
        $data['testInt'] = $fieldValue;
        $data['module'] = 'Test';

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->expectExceptionMessage('Not allowed to edit field testInt in module: Test');

        $this->beanApiHelper->populateFromApi($this->bean, $data);
    }

    public function providerFunction()
    {
        return [
            'default and field value does not match' => [20, 15],
            'default not set but field value set' => [null, 15],
        ];
    }

    public function testNoFieldAccessWithDefaultSetSave()
    {
        $data = [];
        $this->bean->field_defs['testInt']['default'] = 15;
        $data['testInt'] = 15;
        $data['module'] = 'Test';

        $expected = $this->beanApiHelper->populateFromApi($this->bean, $data);
        $this->assertTrue($expected, 'Edit field should be allowed.');
    }
}

class SugarBeanApiHelperNoAccessTest_ServiceMockup extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
