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

class SugarFieldRelateTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @ticket 35265
     */
    public function testFormatContactNameWithoutFirstName()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 'l f');

        $vardef = ['name' => 'contact_name'];
        $value = 'Mertic';

        $sfr = new SugarFieldRelate('relate');

        $this->assertEquals(
            trim($sfr->formatField($value, $vardef)),
            'Mertic'
        );
    }

    /**
     * @ticket 35265
     */
    public function testFormatContactNameThatIsEmpty()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 'l f');

        $vardef = ['name' => 'contact_name'];
        $value = '';

        $sfr = new SugarFieldRelate('relate');

        $this->assertEquals(
            $sfr->formatField($value, $vardef),
            ''
        );
    }

    public function testFormatOtherField()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 'l f');

        $vardef = ['name' => 'account_name'];
        $value = 'John Mertic';

        $sfr = new SugarFieldRelate('relate');

        $this->assertEquals(
            $sfr->formatField($value, $vardef),
            'John Mertic'
        );
    }

    /**
     * @group bug38548
     */
    public function testGetSearchViewSmarty()
    {
        $vardef = [
            'name' => 'assigned_user_id',
            'rname' => 'user_name',
            'id_name' => 'assigned_user_id',
            'vname' => 'LBL_ASSIGNED_TO_ID',
            'group' => 'assigned_user_name',
            'type' => 'relate',
            'table' => 'users',
            'module' => 'Users',
            'reportable' => true,
            'isnull' => 'false',
            'dbType' => 'id',
            'audited' => true,
            'comment' => 'User ID assigned to record',
            'duplicate_merge' => 'disabled',
        ];
        $displayParams = [];
        $sfr = new SugarFieldRelate('relate');
        $output = $sfr->getSearchViewSmarty('fields', $vardef, $displayParams, 0);
        $this->assertStringContainsString('name="{$fields.assigned_user_id', $output);

        $vardef = [
            'name' => 'account_name',
            'rname' => 'name',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'relate',
            'table' => 'accounts',
            'join_name' => 'accounts',
            'isnull' => 'true',
            'module' => 'Accounts',
            'dbType' => 'varchar',
            'link' => 'accounts',
            'len' => '255',
            'source' => 'non-db',
            'unified_search' => true,
            'required' => true,
            'importable' => 'required',
        ];
        $displayParams = [];
        $sfr = new SugarFieldRelate('relate');
        $output = $sfr->getSearchViewSmarty('fields', $vardef, $displayParams, 0);
        $this->assertStringNotContainsString('name="{$fields.account_id', $output);
    }
}
