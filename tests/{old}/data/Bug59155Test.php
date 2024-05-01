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
use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;

require_once 'modules/DynamicFields/FieldCases.php';

/**
 * @ticket 59155
 */
class Bug59155Test extends TestCase
{
    private static $custom_field_def = [
        'formula' => 'related($accounts,"name")',
        'name' => 'bug_59155',
        'type' => 'text',
        'label' => 'LBL_CUSTOM_FIELD',
        'module' => 'ModuleBuilder',
        'view_module' => 'Cases',
    ];

    public static function setUpBeforeClass(): void
    {
        // if shadow is detected, we need to skip this test as it doesn't play nice with shadow
        if (extension_loaded('shadow')) {
            self::markTestSkipped('Does not work on Shadow Enabled System');
        }


        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, 1]);

        $request = InputValidation::create(self::$custom_field_def, []);
        $mbc = new ModuleBuilderController($request);
        $mbc->action_SaveField();

        VardefManager::refreshVardefs('Cases', 'Case');
    }

    public static function tearDownAfterClass(): void
    {
        $custom_field_def = self::$custom_field_def;
        $custom_field_def['name'] .= '_c';

        $request = InputValidation::create($custom_field_def, []);
        $mbc = new ModuleBuilderController($request);
        $mbc->action_DeleteField();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $_REQUEST = [];
        SugarCache::$isCacheReset = false;
    }

    public function testCaseCalcFieldIsConsidered()
    {
        $account = BeanFactory::newBean('Accounts');
        $fields = SugarTestReflection::callProtectedMethod(
            $account,
            'get_fields_influencing_linked_bean_calc_fields',
            ['cases']
        );
        $this->assertContains('name', $fields);
    }
}
