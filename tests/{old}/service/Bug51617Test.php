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

require_once 'modules/DynamicFields/FieldCases.php';

class Bug51617Test extends SOAPTestCase
{
    /**
     * @var mixed
     */
    public $field;
    /**
     * @var \DynamicField|mixed
     */
    public $df;
    /**
     * @var \Account|mixed
     */
    public $mod;
    private $account;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v2/soap.php';

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, 1]);

        $this->field = get_widget('varchar');
        $this->field->id = 'Accountstest_custom_c';
        $this->field->name = 'test_custom_c';
        $this->field->vname = 'LBL_TEST_CUSTOM_C';
        $this->field->comments = null;
        $this->field->help = null;
        $this->field->custom_module = 'Accounts';
        $this->field->type = 'varchar';
        $this->field->label = 'LBL_TEST_CUSTOM_C';
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = null;
        $this->field->date_modified = '2009-09-14 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = null;
        $this->field->ext2 = null;
        $this->field->ext3 = null;
        $this->field->ext4 = null;

        $this->df = new DynamicField('Accounts');
        $this->mod = new Account();
        $this->df->setup($this->mod);
        $this->df->addFieldObject($this->field);
        $this->df->buildCache('Accounts');
        $GLOBALS['db']->commit();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');
        $this->mod->field_defs = $GLOBALS['dictionary']['Account']['fields'];

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->account->test_custom_c = 'Custom Field';
        $this->account->team_set_id = '1';
        $this->account->team_id = '1';
        $this->account->save();

        $GLOBALS['db']->commit(); // Making sure we commit any changes

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->df->deleteField($this->field);
        if ($GLOBALS['db']->tableExists('accounts_cstm')) {
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->account->id}'");
        }

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();

        parent::tearDown();
        $GLOBALS['db']->commit();

        global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        unset($soap_version_test_accountId);
        unset($soap_version_test_opportunityId);
        unset($soap_version_test_contactId);
    }

    /**
     *
     */
    public function testGetEntryListWithCustomField()
    {
        $this->login();
        $GLOBALS['db']->commit();
        $result = $this->soapClient->get_entry_list(
            $this->sessionId,
            'Accounts',
            "accounts.id = '{$this->account->id}'",
            '',
            0,
            ['id', 'name', 'test_custom_c'],
            null,
            1
        );
        $result = object_to_array_deep($result);

        $this->assertTrue(
            $result['result_count'] > 0,
            'Get_entry_list failed: Fault code: ' . $this->soapClient->faultcode . ', fault string:' . $this->soapClient->faultstring . ', fault detail: ' . $this->soapClient->faultdetail
        );

        $row = [];
        $row = $result['entry_list'][0]['name_value_list'];

        // find the custom field
        if (!empty($row)) {
            foreach ($row as $r) {
                // just make sure they are all not empty
                $this->assertNotEmpty($r['value'], 'Value is empty, looks like: ' . var_export($r, true));
                // make sure that the test field has our value in it
                if ($r['name'] == 'test_custom_c') {
                    $this->assertEquals('Custom Field', $r['value'], 'Custom field does not have our value in it');
                }
            }
        } // if
    } // fn
}
