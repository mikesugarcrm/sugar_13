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

class DynamicFieldTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDownCustomFields();
    }

    /**
     * @test
     * @dataProvider typeProvider
     */
    public function template(string $type, $value): void
    {
        SugarTestHelper::setUpCustomField('Accounts', [
            'name' => 'test',
            'type' => $type,
        ]);

        $account = SugarTestAccountUtilities::createAccount(null, [
            'test_c' => $value,
        ]);

        $retrievedAccount = $this->retrieveBean($account);

        $this->assertEquals($value, $retrievedAccount->test_c);
    }

    public static function typeProvider(): iterable
    {
        return [
            'bool' => ['bool', true],
            'encrypt' => ['encrypt', 'Passw0rd'],
            'id' => ['id', '258d050c-3914-11e8-8f26-0242d56b75cb'],
        ];
    }

    private function retrieveBean(SugarBean $bean): SugarBean
    {
        return BeanFactory::retrieveBean($bean->module_name, $bean->id, [
            'use_cache' => false,
        ]);
    }
}
