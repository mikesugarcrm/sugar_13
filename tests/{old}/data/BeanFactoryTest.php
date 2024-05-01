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
 * @coversDefaultClass BeanFactory
 */
class BeanFactoryTest extends TestCase
{
    protected $createdBeans = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        BeanFactory::unsetBeanClass();
        BeanFactory::clearCache();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
    public function testGetBean()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $validBean = BeanFactory::retrieveBean($account->module_name, $account->id);

        $this->assertEquals($account->id, $validBean->id);

        //Ensure we get a false if we try to load a bad bean.
        $uniqueID = uniqid();
        $invalidBean = BeanFactory::retrieveBean($account->module_name, $uniqueID);
        $this->assertFalse(isset($invalidBean->id));
    }

    public function testRegisterBean()
    {
        $account = SugarTestAccountUtilities::createAccount();

        // Test that it is registered
        $registered = $this->isBeanRegistered($account);
        $this->assertTrue($registered, 'Newly created Account bean is not registered');

        // Change the record and get it again
        $account->name = 'BeanFactoryTestHASCHANGED';
        $account->save();

        // Test that the changes took
        $new = BeanFactory::getBean($account->module_name, $account->id);
        $this->assertEquals($account->name, $new->name);
    }

    public function testRegisterNewBean()
    {
        $account = BeanFactory::getBean('Accounts');
        BeanFactory::registerBean($account);
        // Test that it is registered
        $registered = $this->isNewBeanRegistered($account);
        $this->assertTrue($registered, 'Unsaved new bean is not registered in newBeans');
        // Shouldn't be cached in $loadedBeans
        $registered = $this->isBeanRegistered($account);
        $this->assertFalse($registered, 'Unsaved bean is registered in loadedBeans');
    }

    public function testUnregisterNewBean()
    {
        $account = BeanFactory::getBean('Accounts');
        BeanFactory::registerBean($account);
        // Test that it is unregistered
        BeanFactory::unregisterBean($account);
        $registered = $this->isNewBeanRegistered($account);
        $this->assertFalse($registered, 'failed to unregister a unsaved new bean');
        $registered = $this->isBeanRegistered($account);
        $this->assertFalse($registered, 'New bean is still registered in the factory');
    }

    public function testRegisterBeanLegacyStyle()
    {
        $account = SugarTestAccountUtilities::createAccount();

        // Unregister it so we can test registration
        BeanFactory::unregisterBean($account);
        $unregistered = $this->isBeanRegistered($account);
        $this->assertFalse($unregistered, 'New bean is still registered in the factory');

        // Test registration old style way
        $registered = BeanFactory::registerBean($account->module_name, $account, $account->id);
        $this->assertTrue($registered, 'Legacy style registration of the bean failed');

        // Double ensure it worked
        $registered = $this->isBeanRegistered($account);
        $this->assertTrue($registered, 'Legacy style registration did not actually register the bean');
    }

    public function testUnregisterBean()
    {
        $account = SugarTestAccountUtilities::createAccount();

        // Test that unregister is true for a bean
        $unregistered = BeanFactory::unregisterBean($account);
        $this->assertTrue($unregistered, 'Unregister with a bean failed');

        // Test that the bean is no longer in the registry
        $unregistered = $this->isBeanRegistered($account);
        $this->assertFalse($unregistered, 'New bean is still registered in the factory');
    }

    /**
     * Test BeanFactory::getModuleName().
     * @dataProvider providerGetModuleNameByBean
     */
    public function testGetModuleNameByBean($bean, $expectedValue)
    {
        //Test when the function argument is a SugarBean
        $moduleName = BeanFactory::getModuleName($bean);
        $this->assertEquals($moduleName, $expectedValue);
    }

    public function providerGetModuleNameByBean()
    {
        // simulate issue with incorrect module_name property on Filters module
        $filterBean = BeanFactory::newBean('Filters');
        $filterBean->module_name = 'Accounts';

        return [
            [
                BeanFactory::newBean('Accounts'),
                'Accounts',
            ],
            [
                BeanFactory::newBean('Cases'),
                'Cases',
            ],
            [
                BeanFactory::newBean('Users'),
                'Users',
            ],
            [
                new stdClass(),
                false,
            ],
            [
                $filterBean,
                'Filters',
            ],
        ];
    }

    /**
     * Test BeanFactory::getModuleName().
     * @dataProvider providerGetModuleNameByName
     */
    public function testGetModuleNameByName($name, $expectedValue)
    {
        //Test when the function argument is a string (object name)
        $moduleName = BeanFactory::getModuleName($name);
        $this->assertEquals($moduleName, $expectedValue);
    }

    public function providerGetModuleNameByName()
    {
        return [
            [
                'Account',
                'Accounts',
            ],
            [
                'Case',
                'Cases',
            ],
            [
                'User',
                'Users',
            ],
            [
                'aCase',
                false,
            ],
            [
                'Cases',
                false,
            ],
        ];
    }

    /**
     * @covers ::setBeanClass()
     */
    public function testSetBeanClass()
    {
        $this->assertEquals('Contact', BeanFactory::getObjectName('Contacts'));
        $this->assertEquals('Contact', BeanFactory::getBeanClass('Contacts'));

        BeanFactory::setBeanClass('Contacts', 'MyContact');

        $this->assertEquals('Contact', BeanFactory::getObjectName('Contacts'));
        $this->assertEquals('MyContact', BeanFactory::getBeanClass('Contacts'));

        BeanFactory::unsetBeanClass('Contacts');

        $this->assertEquals('Contact', BeanFactory::getObjectName('Contacts'));
        $this->assertEquals('Contact', BeanFactory::getBeanClass('Contacts'));

        $this->assertFalse(BeanFactory::getBeanClass([]));
        $this->assertFalse(BeanFactory::getBeanClass(null));
        $this->assertFalse(BeanFactory::getBeanClass(true));
        $this->assertFalse(BeanFactory::getBeanClass(false));
        $this->assertFalse(BeanFactory::getBeanClass(new stdClass()));
        $this->assertFalse(BeanFactory::getBeanClass('NonExistingClass'));
    }

    /**
     * @test
     */
    public function cacheRespectsDeleted()
    {
        $account = SugarTestAccountUtilities::createAccount();
        BeanFactory::deleteBean($account->module_name, $account->id);
        BeanFactory::clearCache();

        $retrievedAccount1 = $this->retrieveBean($account, [
            'disable_row_level_security' => true,
            'deleted' => false,
        ]);
        $this->assertEquals(1, $retrievedAccount1->deleted);

        $retrievedAccount2 = $this->retrieveBean($account, [
            'disable_row_level_security' => true,
        ]);
        $this->assertNull($retrievedAccount2);
    }

    /**
     * @test
     */
    public function cacheRespectsVisibility()
    {
        $account = SugarTestAccountUtilities::createAccount();
        BeanFactory::clearCache();

        $retrievedAccount1 = $this->retrieveBean($account, ['disable_row_level_security' => true]);
        $retrievedAccount2 = $this->retrieveBean($account);

        $this->assertNotSame($retrievedAccount1, $retrievedAccount2);
    }

    /**
     * @test
     */
    public function cacheRespectsErasedFields()
    {
        $contact = SugarTestContactUtilities::createContact();
        BeanFactory::clearCache();

        $retrievedContact1 = $this->retrieveBean($contact);
        $this->assertNull($retrievedContact1->erased_fields);

        $retrievedContact2 = $this->retrieveBean($contact, ['erased_fields' => true]);
        $this->assertNotNull($retrievedContact2->erased_fields);
    }

    private function retrieveBean(SugarBean $bean, array $params = []): ?SugarBean
    {
        return BeanFactory::retrieveBean($bean->module_name, $bean->id, $params);
    }

    private function isBeanRegistered(SugarBean $bean)
    {
        $loadedBeans = SugarTestReflection::getProtectedValue(BeanFactory::class, 'loadedBeans');

        return isset($loadedBeans[$bean->module_name][$bean->id]);
    }

    private function isNewBeanRegistered(SugarBean $bean)
    {
        $newBeans = SugarTestReflection::getProtectedValue(BeanFactory::class, 'newBeans');
        return isset($newBeans[$bean->module_name][$bean->id]);
    }
}
