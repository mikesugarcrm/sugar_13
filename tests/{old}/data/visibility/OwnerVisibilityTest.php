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

class OwnerVisibilityTest extends TestCase
{
    private $account;

    protected function setUp(): void
    {
        /** @var User $user */
        $user = SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount(null, [
            'assigned_user_id' => $user->id,
        ]);

        SugarTestAccountUtilities::createAccount(null, [
            'assigned_user_id' => create_guid(),
        ]);
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testSugarQuery()
    {
        $query = new SugarQuery();
        $query->from($this->account, [
            'team_security' => false,
        ]);
        $query->select('id');

        $visibility = new OwnerVisibility($this->account);
        $visibility->addVisibilityQuery($query);

        $data = $query->execute();

        $this->assertVisibilityApplied($data);
    }

    public function testSql()
    {
        $query = 'SELECT id FROM accounts WHERE deleted = 0';

        $visibility = new OwnerVisibility($this->account);
        $visibility->addVisibilityFrom($query);
        $visibility->addVisibilityWhere($query);

        $conn = DBManagerFactory::getConnection();
        $data = $conn->executeQuery($query)->fetchAllAssociative();

        $this->assertVisibilityApplied($data);
    }

    private function assertVisibilityApplied(array $data)
    {
        $this->assertCount(1, $data);
        $row = array_shift($data);

        $this->assertSame($this->account->id, $row['id']);
    }
}
