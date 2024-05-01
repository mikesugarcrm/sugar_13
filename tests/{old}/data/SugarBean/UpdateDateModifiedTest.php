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

class UpdateDateModifiedTest extends TestCase
{
    private static $hook = [
        'Accounts',
        'before_save',
        [1, 'Accounts::before_save', __FILE__, 'UpdateDateModifiedTestHook', 'beforeSave'],
    ];

    public static function setUpBeforeClass(): void
    {
        global $disable_date_format;
        $disable_date_format = true;

        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');
    }

    public static function tearDownAfterClass(): void
    {
        unset($GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED']);

        self::tearDownHook();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    private static function setUpHook()
    {
        LogicHook::refreshHooks();
        call_user_func_array('check_logic_hook_file', self::$hook);
    }

    private static function tearDownHook()
    {
        call_user_func_array('remove_logic_hook', self::$hook);
    }

    public function testUpdateDateModified()
    {
        global $timedate;
        unset($GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED']);

        $original_date = '2015-06-02 11:24:00';
        $current_date = '2015-08-13 18:13:00';

        $time = $timedate->fromString($current_date);
        $timedate->setNow($time);

        $account = SugarTestAccountUtilities::createAccount();
        $account->date_modified = $original_date;
        $account->update_date_modified = false;
        $account->save();

        $account = BeanFactory::getBean('Accounts', $account->id, ['use_cache' => false]);
        self::setUpHook();
        $account->save();
        $this->assertEquals(
            $current_date,
            $GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED'],
            'Logic hook should have been called with the current date'
        );

        $account = BeanFactory::getBean('Accounts', $account->id, ['use_cache' => false]);
        $this->assertEquals(
            $original_date,
            $account->date_modified,
            'The modification date should have remained unchanged'
        );
    }
}

class UpdateDateModifiedTestHook
{
    public function beforeSave($bean)
    {
        $GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED'] = $bean->date_modified;
        $bean->update_date_modified = false;
    }
}
