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
 * Make sure that the resave pulls all beans from cache again
 * so it's not stuck with an old version of a bean overwriting changes
 */
class ResaveRelatedBeansTest extends TestCase
{
    private $id;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->id = 'ResaveRelatedBeansTestId';
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testResaveRelatedBeans()
    {
        // Create account and add it to resave list
        $account = SugarTestAccountUtilities::createAccount($this->id);
        SugarRelationship::addToResaveList($account);

        // Now update some data for the account
        $account = BeanFactory::newBean('Accounts');
        $account->id = $this->id;
        $account->name = 'New Name';
        $account->save();

        // And the resave fires after the save()
        SugarRelationship::resaveRelatedBeans();
        // Let's make sure that the latest changes don't get overwritten by an old queued version of the bean
        $savedAccount = BeanFactory::getBean('Accounts', $this->id);

        $this->assertEquals(
            $account->name,
            $savedAccount->name,
            'resaveRelatedBeans() should pull in the latest version from the cache'
        );
    }
}
