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

class RetrieveRetryTest extends TestCase
{
    private $account = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('dictionary');
        $GLOBALS['current_user']->is_admin = 1;
        $this->account = SugarTestAccountUtilities::createAccount('test_acc');
        $dict = [];
        $dict['Account']['fields']['test_field'] =
            [
                'name' => 'test_field',
                'vname' => 'LBL_ID',
                'type' => 'id',
                'required' => false,
                'reportable' => true,
                'default' => '',
            ];
        $extPath = 'custom/Extension/modules/Accounts/Ext/Vardefs/';
        sugar_mkdir($extPath, null, true);
        sugar_file_put_contents_atomic(
            $extPath . 'test_field.php',
            '<?php' . PHP_EOL . override_value_to_string_recursive2('dictionary', 'Account', $dict['Account'])
        );
        $rac = new RepairAndClear();
        $rac->repairAndClearAll(['rebuildExtensions'], ['Accounts'], false, false);
        $GLOBALS['installing'] = true;
        VardefManager::clearVardef('Accounts', 'Account');
        $GLOBALS['installing'] = false;

        $this->iniSet('error_reporting', (string)(E_ALL & ~E_WARNING));
    }

    protected function tearDown(): void
    {
        if (file_exists('custom/Extension/modules/Accounts/Ext/Vardefs/test_field.php')) {
            unlink('custom/Extension/modules/Accounts/Ext/Vardefs/test_field.php');
        }
        if (file_exists('custom/modules/Accounts/Ext/Vardefs/vardefs.ext.php')) {
            unlink('custom/modules/Accounts/Ext/Vardefs/vardefs.ext.php');
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $GLOBALS['installing'] = true;
        VardefManager::clearVardef('Accounts', 'Account');
        $GLOBALS['installing'] = false;
        SugarTestHelper::tearDown();
        $this->account = null;
        parent::tearDown();
    }

    public function testNewBeanWithNewField()
    {
        $acc = BeanFactory::retrieveBean('Accounts', $this->account->id, ['use_cache' => false]);
        $this->assertEquals($this->account->id, $acc->id);
    }

    public function testRetrieveWithNewField()
    {
        $acc = BeanFactory::newBean('Accounts');
        $acc->retrieve($this->account->id);
        $this->assertEquals($this->account->id, $acc->id);
    }
}
