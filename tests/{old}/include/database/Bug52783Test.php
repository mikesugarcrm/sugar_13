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
 * Test is used for testing returnOrderBy() through limitQuery() because returnOrderBy() is private
 * In MSSQL we use a different way of limiting a query using TOP and ROW_NUMBER() OVER( ORDER BY ordering )
 * the 'ordering' attribute is tested on correctness with this test
 *
 * @author avucinic@sugarcrm.com
 */
class Bug52783Test extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = new SqlsrvManager();
    }

    /**
     * Test if returnOrderBy() inside limitQuery() method returns a valid MSSQL ordering column for use in OVER()
     * @dataProvider orderProvider
     */
    public function testReturnOrderBy($sql, $expected)
    {
        $expected = strtolower($expected);
        $result = strtolower($this->db->limitQuery($sql, 21, 20, false, '', false));
        $this->assertStringContainsString($expected, $result);
    }

    public function orderProvider()
    {
        return [
            0 => [
                "SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id my_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name",

                "LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,'')))",
            ],
            1 => [
                "SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id my_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name ASC",

                "LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,'')))",
            ],
            2 => [
                "SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id my_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY contacts.name ASC",

                'contacts.name',
            ],
            3 => [
                "SELECT  contacts.id , ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id my_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name ASC",

                "ISNULL(contacts.last_name,'')))",
            ],
            // CRYS-676. Alias isn't converted when an order string contains a point or more than one part.
            [
                'SELECT contacts.id as id_alias, contacts.title as title_alias
                 FROM contacts
                 WHERE contacts.deleted = 0
                 ORDER BY id_alias ASC,title_alias ASC',
                'ORDER BY contacts.id ASC,contacts.title ASC',
            ],
        ];
    }
}
