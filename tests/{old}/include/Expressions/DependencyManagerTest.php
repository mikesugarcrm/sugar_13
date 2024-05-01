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

class DependencyManagerTest extends TestCase
{
    public $removeCustomDir = false;
    public $cf_test_field = [
        'name' => 'cf_field',
        'type' => 'varchar',
        'calculated' => true,
        'formula' => 'strlen($name)',
    ];
    public $cf_enforced_field = [
        'name' => 'cf_enforced_field',
        'type' => 'varchar',
        'calculated' => true,
        'formula' => 'strlen($name)',
        'enforced' => true,
    ];
    public $dep_field = [
        'name' => 'dep_field',
        'type' => 'varchar',
        'dependency' => 'strlen($name)',
    ];
    public $dd_field = [
        'name' => 'dd_field',
        'type' => 'enum',
        'options' => 'dd_options',
        'visibility_grid' => [
            'trigger' => 'dd_trigger',
            'values' => [
                'one' => ['foo'],
                'two' => ['foo', 'bar'],
            ],
        ],
    ];
    public $dd_trigger = [
        'name' => 'dd_trigger',
        'type' => 'enum',
        'options' => 'dd_trigger_options',
    ];
    public $dd_options = [
        'foo' => 'Foo',
        'bar' => 'Bar',
    ];
    public $dd_trigger_options = [
        'one' => 'One',
        'two' => 'Two',
    ];


    //Final order for these should be cf1, cf2, cf5, cf3, cf4
    public $reliantCalcFields = [
        'cf1' => [
            'name' => 'cf1',
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add(1,1)',
        ],
        'cf2' => [
            'name' => 'cf2',
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf1,1)',
        ],
        'cf3' => [
            'name' => 'cf3',
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf5, $cf2)',
        ],
        'cf4' => [
            'name' => 'cf4',
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf2, $cf5, $cf3)',
        ],
        'cf5' => [
            'name' => 'cf5',
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf2, 1)',
        ],
    ];


    public function testCFDeps()
    {
        $fields = [
            $this->cf_test_field['name'] => $this->cf_test_field,
        ];
        $deps = DependencyManager::getCalculatedFieldDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        //Assert instance of seems to not be definied for the current phpunit version in sugar
        //$this->assertInstanceOf("Dependency", $dep);
        $this->assertFalse($dep->getFireOnLoad());

        $def = $dep->getDefinition();
        $this->assertFalse(empty($def['actions']));
        $aDef = $def['actions'][0];
        $this->assertEquals('SetValue', $aDef['action']);
        $this->assertEquals($this->cf_test_field['name'], $aDef['params']['target']);
        $this->assertEquals($this->cf_test_field['formula'], $aDef['params']['value']);
    }

    public function testCFEnforced()
    {
        $fields = [
            $this->cf_enforced_field['name'] => $this->cf_enforced_field,
        ];
        $deps = DependencyManager::getCalculatedFieldDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        $this->assertFalse($dep->getFireOnLoad());
        $dep = $deps[1];
        $this->assertTrue($dep->getFireOnLoad());
    }

    public function testDepFieldDeps()
    {
        $fields = [
            $this->dep_field['name'] => $this->dep_field,
        ];
        $deps = DependencyManager::getDependentFieldDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        //Assert instance of seems to not be definied for the current phpunit version in sugar
        //$this->assertInstanceOf("Dependency", $dep);
        $this->assertTrue($dep->getFireOnLoad());

        $def = $dep->getDefinition();
        $this->assertFalse(empty($def['actions']));
        $aDef = $def['actions'][0];
        $this->assertEquals('SetVisibility', $aDef['action']);
        $this->assertEquals($this->dep_field['name'], $aDef['params']['target']);
        $this->assertEquals($this->dep_field['dependency'], $aDef['params']['value']);
    }

    public function testDropDownDeps()
    {
        global $app_list_strings;
        $app_list_strings['dd_trigger_options'] = $this->dd_trigger_options;
        $app_list_strings['dd_options'] = $this->dd_options;
        $fields = [
            $this->dd_field['name'] => $this->dd_field,
            $this->dd_trigger['name'] => $this->dd_trigger,
        ];

        $deps = DependencyManager::getDropDownDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        //Assert instance of seems to not be definied for the current phpunit version in sugar
        //$this->assertInstanceOf("Dependency", $dep);
        $this->assertTrue($dep->getFireOnLoad());
        $def = $dep->getDefinition();
        $this->assertFalse(empty($def['actions']));
        $aDef = $def['actions'][0];

        $this->assertEquals('SetOptions', $aDef['action']);

        $expectedKeys = 'getListWhere($dd_trigger, enum(enum("one", enum("foo")),enum("two", enum("foo","bar"))))';
        $expectedLabels = '"dd_options"';
        $this->assertEquals($this->dd_field['name'], $aDef['params']['target']);
        $this->assertEquals($expectedKeys, $aDef['params']['keys']);
        $this->assertEquals($expectedLabels, $aDef['params']['labels']);
    }

    public function testCalculatedOrdering()
    {
        $deps = DependencyManager::getCalculatedFieldDependencies($this->reliantCalcFields, false, true);
        $expectedOrder = ['cf1', 'cf2', 'cf5', 'cf3', 'cf4'];
        foreach ($deps as $i => $dep) {
            $def = $dep->getDefinition();
            $this->assertEquals($def['name'], $expectedOrder[$i]);
        }
    }

    public function dataProviderGetDependenciesForView()
    {
        return [
            ['view', 'DetailView'],
            ['edit', 'RecordView'],
            ['edit', 'EditView'],
            ['edit', 'CreateView'],
            ['edit', 'AccountsQuickCreateView'],
        ];
    }

    /**
     * @dataProvider dataProviderGetDependenciesForView
     *
     * @param $type
     * @param $view_name
     */
    public function testGetDependenciesForView($type, $view_name)
    {
        $class = new MockDependencyManager();

        $ret = $class::getDependenciesForView([], $view_name, 'Accounts');

        $expected = [
            'module' => 'Accounts',
            'type' => $type,
            'view' => $view_name,
        ];

        $this->assertEquals($expected, $ret);
    }

    public function dataProviderGetRequiredFieldDependencies()
    {
        return [
            [
                [
                    'foo' => [
                        'required' => true,
                        'required_formula' => 'bar',
                        'calculated' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGetRequiredFieldDependencies
     *
     * @param $fields
     */
    public function testGetRequiredFieldDependencies($fields)
    {
        $ret = DependencyManager::getRequiredFieldDependencies($fields);

        $this->assertInstanceOf('Dependency', $ret[0]);
    }
}

class MockDependencyManager extends DependencyManager
{
    public static function getModuleDependenciesForAction($module, $action, $form = 'EditView')
    {
        return [
            'module' => $module,
            'type' => $action,
            'view' => $form,
        ];
    }
}
