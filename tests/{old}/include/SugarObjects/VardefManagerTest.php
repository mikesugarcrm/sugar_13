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
 * @coversDefaultClass VardefManager
 */
class VardefManagerTest extends TestCase
{
    protected $module = 'Tests';
    protected $object = 'Test';
    protected $objectName = 'test';

    protected function setUp(): void
    {
        // For testing table name getting
        $GLOBALS['dictionary']['Hillbilly']['table'] = 'hillbillies';
    }

    protected function tearDown(): void
    {
        // Get rid of the globals stuff that was setup just for this test
        unset($GLOBALS['dictionary']['Hillbilly']);

        // Let the parent finish things up
    }

    /**
     * Tests the getTemplates method of the VardefManager. The tested method is
     * wrapped in cache clear calls to prevent downstream tests from suffering.
     *
     * @dataProvider providerGetTemplates
     */
    public function testGetTemplates($module, $object, $template, $object_name, $expect)
    {
        // Clear the fetched templates cache first
        VardefManager::clearFetchedTemplates();

        // Grab the templates for this test
        $actual = VardefManager::getTemplates($module, $object, $template, $object_name);

        // Clear the fetched templates cache again to make sure it doesn't mess
        // things up in later tests
        VardefManager::clearFetchedTemplates();

        // Handle assertions
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests getting all templates recursively
     *
     * @dataProvider providerAccountTemplates
     */
    public function testGetAllTemplates($templates, $module, $object, $expect)
    {
        $actual = VardefManager::getAllTemplates($templates, $module, $object);

        // Assert emptiness of array diff, since order is NOT important in this
        // case and an assertEquals will check order as well
        $this->assertEmpty(array_diff($expect['core'], $actual[0]));
        $this->assertEmpty(array_diff($expect['impl'], $actual[1]));
    }

    /**
     * Tests getting the proper object name
     *
     * @dataProvider providerGetObjectName
     */
    public function testGetObjectName($object, $name, $nameOnly, $expect)
    {
        $actual = VardefManager::getObjectName($object, $name, $nameOnly);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests getting the proper table name
     *
     * @dataProvider providerGetTableName
     */
    public function testGetTableName($module, $object, $expect)
    {
        $actual = VardefManager::getTableName($module, $object);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests getting all loadable templates in the correct order
     *
     * @dataProvider providerLoadableTemplates
     */
    public function testGetLoadableTemplates($templates, $module, $object, $expect)
    {
        $actual = VardefManager::getLoadableTemplates($templates, $module, $object);
        $this->assertEquals($expect, $actual);
    }

    public function testGetCoreTemplates()
    {
        $expect = [
            'default',
            'basic',
            'company',
            'file',
            'issue',
            'person',
            'sale',
        ];

        $actual = VardefManager::getCoreTemplates();
        $this->assertEquals($expect, $actual);
    }

    public function providerGetTemplates()
    {
        return [
            // Tests handling of Person template
            [
                'module' => $this->module,
                'object' => $this->object,
                'template' => 'person',
                'object_name' => $this->objectName,
                'expect' => [
                    'person',
                    'email_address',
                    'taggable',
                    'audit',
                ],
            ],
            // Tests handling of 'default' template
            [
                'module' => $this->module,
                'object' => $this->object,
                'template' => 'default',
                'object_name' => $this->objectName,
                'expect' => [
                    'basic',
                    'following',
                    'favorite',
                    'taggable',
                    'commentlog',
                    'lockable_fields',
                    'integrate_fields',
                    'audit',
                ],
            ],
        ];
    }

    public function providerGetObjectName()
    {
        return [
            // Tests passing in of object name with mutation
            [
                'object' => $this->module,
                'name' => $this->object,
                'nameOnly' => false,
                'expect' => $this->objectName,
            ],
            // Tests passing in of object name with no mutation
            [
                'object' => $this->module,
                'name' => $this->object,
                'nameOnly' => true,
                'expect' => $this->object,
            ],
            // Tests not passing in of object name with mutation
            [
                'object' => $this->module,
                'name' => '',
                'nameOnly' => false,
                'expect' => 'tests',
            ],
            // Tests not passing in of object name with NO mutation
            [
                'object' => $this->module,
                'name' => '',
                'nameOnly' => true,
                'expect' => $this->module,
            ],
        ];
    }

    public function providerGetTableName()
    {
        return [
            // Tests no vardef defined
            [
                'module' => 'Hucksters',
                'object' => 'Huckster',
                'expect' => 'hucksters',
            ],
            // Tests vardef defined
            [
                'module' => 'Womprats',
                'object' => 'Hillbilly',
                'expect' => 'hillbillies',
            ],
        ];
    }

    public function providerLoadableTemplates()
    {
        return [
            [
                'templates' => [
                    'default',
                    'assignable',
                    'team_security',
                    'company',
                ],
                'module' => 'Accounts',
                'object' => 'Account',
                'expect' => [
                    'company',
                    'basic',
                    'following',
                    'favorite',
                    'taggable',
                    'commentlog',
                    'lockable_fields',
                    'integrate_fields',
                    'audit',
                    'assignable',
                    'team_security',
                    'email_address',
                ],
            ],
        ];
    }

    public function providerAccountTemplates()
    {
        return [
            [
                'templates' => [
                    'default',
                    'assignable',
                    'team_security',
                    'company',
                ],
                'module' => 'Accounts',
                'object' => 'Account',
                'expect' => [
                    'core' => [
                        'company',
                        'basic',
                    ],
                    'impl' => [
                        'following',
                        'favorite',
                        'taggable',
                        'assignable',
                        'team_security',
                        'email_address',
                        'commentlog',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::getLinkFieldsForCollection
     */
    public function testGetLinkFieldsForCollection()
    {
        $expected = [
            'contacts',
            'leads',
            'users',
        ];
        $actual = VardefManager::getLinkFieldsForCollection('Meetings', 'Meeting', 'invitees');
        $this->assertEquals($expected, $actual);
    }

    public function usesTemplateProvider()
    {
        return [
            [
                'Contacts',
                'assignable',
                true,
            ],
            [
                'Contacts',
                'basic',
                true,
            ],
            [
                'Contacts',
                'favorite',
                true,
            ],
            [
                'Contacts',
                'email_address',
                true,
            ],
            [
                'Contacts',
                'person',
                true,
            ],
            [
                'Contacts',
                'taggable',
                true,
            ],
            [
                '',
                'basic',
                false,
            ],
            [
                'Contacts',
                '',
                false,
            ],
            [
                'Contacts',
                'company',
                false,
            ],
            [
                'ACLRole',
                'basic',
                false,
            ],
            [
                'Calls',
                'person',
                false,
            ],
            [
                'Foo',
                'basic',
                false,
            ],
            [
                'Tasks',
                'issue',
                false,
            ],
            [
                'Contacts',
                [
                    'assignable',
                    'person',
                ],
                true,
            ],
            [
                'Contacts',
                [
                    'person',
                    'company',
                ],
                false,
            ],
        ];
    }

    /**
     * @covers ::usesTemplate
     * @dataProvider usesTemplateProvider
     */
    public function testUsesTemplate($module, $template, $expected)
    {
        $actual = VardefManager::usesTemplate($module, $template);
        $this->assertSame($expected, $actual);
    }
}
