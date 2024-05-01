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

require_once 'include/dir_inc.php';

class MassUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    /**
     * @ticket 12300
     */
    public function testAdddateWorksWithMultiByteCharacters()
    {
        $mass = new MassUpdate();
        $displayname = '开始日期:';
        $varname = 'date_start';

        $result = $mass->addDate($displayname, $varname);
        $pos_f = strrpos($result, (string) $GLOBALS['app_strings']['LBL_MASSUPDATE_DATE']);
        $this->assertTrue((bool)$pos_f);
    }

    /**
     * @ticket 23900
     */
    public function testAddStatus()
    {
        $this->markTestSkipped('Testing super old code and the test is failing on PHP7.4');
        $mass = new MassUpdate();
        $options = [
            '' => '',
            '10' => 'ten',
            '20' => 'twenty',
            '30' => 'thirty',
        ];
        $result = $mass->addStatus('test_dom', 'test_dom', $options);
        preg_match_all('/value=[\'\"].*?[\'\"]/si', $result, $matches);
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][1] == "value='__SugarMassUpdateClearField__'");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'");
        $this->assertTrue($matches[0][4] == "value='30'");
    }

    /**
     * @ticket 23900
     */
    public function testAddStatusMulti()
    {
        $this->markTestSkipped('Testing super old code and the test is failing on PHP7.4');
        $mass = new MassUpdate();
        $options = [
            '10' => 'ten',
            '20' => 'twenty',
            '30' => 'thirty',
        ];

        $result = $mass->addStatusMulti('test_dom', 'test_dom', $options);
        preg_match_all('/value=[\'\"].*?[\'\"]/si', $result, $matches);
        $this->assertTrue(isset($matches));
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][1] == "value='__SugarMassUpdateClearField__'");
        $this->assertTrue($matches[0][2] == "value='10'");
        $this->assertTrue($matches[0][3] == "value='20'");
        $this->assertTrue($matches[0][4] == "value='30'");
    }

    public function testAddStatusMultiNoNullOption()
    {
        $this->markTestSkipped('Testing super old code and the test is failing on PHP7.4');
        $mass = new MassUpdate();
        $options = [
            '10' => 'ten',
            '20' => 'twenty',
            '30' => 'thirty',
        ];

        $result = $mass->addStatusMulti('test_dom', 'test_dom', $options, false);
        preg_match_all('/value=[\'\"].*?[\'\"]/si', $result, $matches);
        $this->assertTrue(isset($matches));
        $this->assertTrue($matches[0][0] == "value=''");
        $this->assertTrue($matches[0][1] == "value='10'");
        $this->assertTrue($matches[0][2] == "value='20'");
        $this->assertTrue($matches[0][3] == "value='30'");
    }

    /**
     * @dataProvider setMassUpdateFielddefsProvider
     * @param $fielddefs
     * @param $module
     * @param $resultFieldDefs
     */
    public function testSetMassUpdateFielddefs($fielddefs, $module, $resultFieldDefs)
    {
        $result = MassUpdate::setMassUpdateFielddefs($fielddefs, $module);
        $this->assertEquals($resultFieldDefs, $result);
    }

    public function setMassUpdateFielddefsProvider()
    {
        return [
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'readonly' => true,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'readonly' => true, 'massupdate' => false,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => true,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => true, 'massupdate' => false,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => false,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'calculated' => true, 'enforced' => false, 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => true,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => 'true',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => 1,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => 0,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => false,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => 'false',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => false,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'date_modified',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'date_modified',
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'contact_id',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'contact_id', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'assigned_user_name',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'assigned_user_name', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'account_id',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'account_id', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'account_name',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'account_name', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'bool', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'parent',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'parent', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'enum',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'enum', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'multienum',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'multienum', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'radioenum',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'radioenum', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'datetime',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'datetime', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'date',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'date', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'int', 'massupdate' => true,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'int', 'massupdate' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'int', 'auto_increment' => true,
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'int', 'auto_increment' => true,
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'relate',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'relate',
                    ],
                ],
            ],
            [
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'relate', 'id_name' => 'bar',
                    ],
                ],
                'Foo',
                [
                    'test' => [
                        'name' => 'foofield', 'type' => 'relate', 'id_name' => 'bar', 'massupdate' => true,
                    ],
                ],
            ],
            [
                ['sync_contact' => []],
                'Contacts',
                [
                    'sync_contact' => [
                        'massupdate' => true,
                    ],
                ],
            ],
            [
                ['employee_status' => []],
                'Employees',
                [
                    'employee_status' => [
                        'massupdate' => true, 'type' => 'enum', 'options' => 'employee_status_dom',
                    ],
                ],
            ],
            [
                ['status' => []],
                'InboundEmail',
                [
                    'status' => [
                        'massupdate' => true, 'type' => 'enum', 'options' => 'user_status_dom',
                    ],
                ],
            ],
        ];
    }
}
