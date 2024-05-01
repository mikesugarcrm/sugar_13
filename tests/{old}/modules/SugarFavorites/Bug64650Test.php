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
 * @ticket 64650
 */
class Bug64650Test extends TestCase
{
    /** @var Account */
    private $account;

    /** @var SugarFavorites */
    private $favorite;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->favorite = new SugarFavorites();
        $this->favorite->new_with_id = true;
        $this->favorite->id = SugarFavorites::generateGUID('Accounts', $this->account->id);
        $this->favorite->module = 'Accounts';
        $this->favorite->record_id = $this->account->id;
        $this->favorite->save();
    }

    protected function tearDown(): void
    {
        $db = $this->favorite->db;
        $db->query(
            'DELETE FROM sugarfavorites WHERE id = '
            . $db->quoted($this->favorite->id)
        );

        SugarTestAccountUtilities::removeAllCreatedAccounts();

        SugarTestHelper::tearDown();
    }

    public function testFavorite()
    {
        $bean = BeanFactory::newBean('Accounts');

        $where = $bean->table_name . ".id = '{$this->account->id}'";
        $query = $bean->create_new_list_query('id', $where, [], [
            'favorites' => 1,
        ]);

        $db = $bean->db;
        $dbResult = $db->query($query);
        $row = $db->fetchRow($dbResult);

        $row = $bean->convertRow($row);
        $bean->populateFromRow($row);

        $this->assertTrue($bean->my_favorite);
    }
}
