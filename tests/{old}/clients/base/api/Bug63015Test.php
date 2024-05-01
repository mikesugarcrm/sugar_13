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
class Bug63015Test extends TestCase
{
    /**
     * @var ModuleApi
     */
    public $moduleApi;

    public $serviceMock;
    public $accountIds;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->moduleApi = new ModuleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'ModulaApiTest setUp Account';
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accountIds[] = $account->id;
    }

    protected function tearDown(): void
    {
        // delete the bunch of accounts created
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id in('" . implode("','", $this->accountIds) . "')");
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testCreateWithExistingId()
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);

        // try to create a record with an existing id
        $this->moduleApi->createRecord($this->serviceMock, [
            'module' => 'Accounts',
            'name' => 'Test Account1',
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'id' => $this->accountIds[0],
        ]);
    }

    public function testCreateWithNewId()
    {
        $id = create_guid();
        $this->accountIds[] = $id;
        // create a record
        $result = $this->moduleApi->createRecord($this->serviceMock, ['module' => 'Accounts', 'name' => 'Test Account2', 'assigned_user_id' => $GLOBALS['current_user']->id, 'id' => $id]);
        // verify same id is returned
        $this->assertEquals($id, $result['id']);
    }
}
