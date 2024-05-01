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

class ActivitiesApiHelperTest extends TestCase
{
    public function dataProviderForGetDisplayModule()
    {
        $emptyAccount = BeanFactory::newBean('Accounts');
        $emptyLead = BeanFactory::newBean('Leads');
        return [
            ['post', null, 'Accounts', '123'],
            ['post', $emptyAccount, 'Accounts', '123'],
            ['post', $emptyLead, 'Accounts', '123'],
            ['link', null, 'Accounts', '123'],
            ['link', $emptyAccount, 'Leads', '456'],
            ['link', $emptyLead, 'Accounts', '123'],
            ['unlink', null, 'Accounts', '123'],
            ['unlink', $emptyAccount, 'Leads', '456'],
            ['unlink', $emptyLead, 'Accounts', '123'],
        ];
    }

    /**
     * @covers       ActivitiesApiHelper::getDisplayModule
     * @dataProvider dataProviderForGetDisplayModule
     */
    public function testGetDisplayModule($activity_type, $contextBean, $expected_module, $expected_id)
    {
        $record = [
            'parent_type' => 'Accounts',
            'parent_id' => '123',
            'activity_type' => $activity_type,
            'data' => [
                'subject' => [
                    'module' => 'Leads',
                    'id' => '456',
                ],
            ],
        ];

        $helper = new ActivitiesApiHelper(new ActivitiesServiceMockup());
        $result = SugarTestReflection::callProtectedMethod($helper, 'getDisplayModule', [$record, $contextBean]);

        $this->assertEquals($expected_module, $result['module']);
        $this->assertEquals($expected_id, $result['id']);
    }
}

class ActivitiesServiceMockup extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
