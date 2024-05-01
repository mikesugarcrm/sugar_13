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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;

class Bug61736Test extends TestCase
{
    /**
     * The custom vardef file created during the test
     *
     * @var string
     */
    private static $vardefFile = 'custom/modulebuilder/packages/p1/modules/bbb/vardefs.php';

    /**
     * Mock REQUEST array used to create the test package
     *
     * @var array
     */
    private static $createPackageRequestVars = [
        'name' => 'p1',
        'description' => '',
        'author' => '',
        'key' => 'p0001',
        'readme' => '',
    ];

    /**
     * Mock REQUEST array used to create the test module
     *
     * @var array
     */
    private static $createModuleRequestVars = [
        'name' => 'bbb',
        'label' => 'BBB',
        'label_singular' => 'BBB',
        'package' => 'p1',
        'has_tab' => '1',
        'type' => 'basic',
    ];

    /**
     * Mock request for creating the field
     *
     * @var array
     */
    private static $createFieldRequestVars = [
        'labelValue' => 'Basic Address',
        'label' => 'LBL_BASIC_ADDRESS',
        'type' => 'address',
        'name' => 'basic_address',
        'view_module' => 'bbb',
        'view_package' => 'p1',
    ];

    /**
     * Mock request for deleting the field
     *
     * @var array
     */
    private static $deleteFieldRequestVars = [
        'labelValue' => 'Basic Address',
        'label' => 'LBL_BASIC_ADDRESS',
        'to_pdf' => 'true',
        'type' => 'varchar',
        'name' => 'basic_address',
        'view_module' => 'bbb',
        'view_package' => 'p1',
    ];

    public static function setUpBeforeClass(): void
    {
        // Basic setup of the environment
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

        // Create the package
        $request = InputValidation::create(self::$createPackageRequestVars, []);
        $mbc = new ModuleBuilderController($request);
        $mbc->action_SavePackage();

        // Now create the module
        $request = InputValidation::create(self::$createModuleRequestVars, []);
        $mbc = new ModuleBuilderController($request);
        $mbc->action_SaveModule();

        // Now create the address field
        $request = InputValidation::create(self::$createFieldRequestVars, []);
        $mbc = new ModuleBuilderController($request);
        $mbc->action_SaveField();
    }

    public static function tearDownAfterClass(): void
    {
        // Set the request to delete the test field
        $vars = self::$deleteFieldRequestVars;

        // Loop through the created fields and wipe them out
        $suffixes = ['street', 'city', 'state', 'postalcode', 'country'];
        foreach ($suffixes as $suffix) {
            $vars['name'] = self::getFieldName($suffix);
            $request = InputValidation::create($vars, []);
            $mbc = new ModuleBuilderController($request);
            $mbc->action_DeleteField();
        }

        // Delete the custom module
        $vars = self::$createModuleRequestVars;
        $vars['view_module'] = 'bbb';
        $request = InputValidation::create($vars, []);
        $mbc = new ModuleBuilderController($request);
        $mbc->action_DeleteModule();

        // Delete the custom package
        $vars = self::$createPackageRequestVars;
        $vars['package'] = $vars['name'];
        $request = InputValidation::create($vars, []);
        $mbc = new ModuleBuilderController($request);
        $mbc->action_DeletePackage();
    }

    public function testCustomAddressFieldVardefFileCreated()
    {
        $this->assertFileExists(self::$vardefFile, 'The custom field vardef for the new module was not found');
    }

    private static function getFieldName($suffix)
    {
        $field = self::$createFieldRequestVars['name'];
        $name = $field . '_' . $suffix;
        return $name;
    }
}
