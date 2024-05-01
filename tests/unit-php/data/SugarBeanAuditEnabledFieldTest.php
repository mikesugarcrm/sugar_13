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

namespace Sugarcrm\SugarcrmTestsUnit\data\SugarBeanAuditEnabledFieldTest;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarBean
 */
class SugarBeanAuditEnabledFieldTest extends TestCase
{
    private $testBean;

    public function setUp(): void
    {
        $this->testBean = $this->getMockBuilder(\SugarBean::class)
            ->disableOriginalConstructor()
            ->setMethods(['ACLFieldAccess'])
            ->getMock();
    }

    /**
     * @covers ::getAuditEnabledFieldDefinitions
     * @dataProvider providerTestGetFirstAuditEnabledFieldDefinitions
     */
    public function testGetFirstAuditEnabledFieldDefinitions($vardef, $expected)
    {
        $this->testBean->field_defs['test'] = $vardef;
        $this->testBean->expects($this->any())
            ->method('ACLFieldAccess')
            ->willReturn(true);

        $this->assertSame($expected, $this->testBean->getAuditEnabledFieldDefinitions(true));
    }

    /**
     * @covers ::getAuditEnabledFieldDefinitions
     * @dataProvider providerTestGetSecondAuditEnabledFieldDefinitions
     */
    public function testGetSecondAuditEnabledFieldDefinitions($vardef, $expected)
    {
        $this->testBean->field_defs['test'] = $vardef;
        $this->testBean->expects($this->any())
            ->method('ACLFieldAccess')
            ->willReturn(false);

        $this->assertSame($expected, $this->testBean->getAuditEnabledFieldDefinitions(false));
    }

    /**
     * @covers ::getAuditEnabledFieldDefinitions
     * @dataProvider providerTestGetThirdAuditEnabledFieldDefinitions
     */
    public function testGetThirdAuditEnabledFieldDefinitions($vardef, $expected)
    {
        $this->testBean->field_defs['test'] = $vardef;
        $this->testBean->expects($this->any())
            ->method('ACLFieldAccess')
            ->willReturn(false);

        $this->assertSame($expected, $this->testBean->getAuditEnabledFieldDefinitions(true));
    }

    /**
     * @covers ::getAuditEnabledFieldDefinitions
     * @dataProvider providerTestGetFourthAuditEnabledFieldDefinitions
     */
    public function testGetFourthAuditEnabledFieldDefinitions($vardef, $expected)
    {
        $this->testBean->field_defs['test'] = $vardef;
        $this->testBean->expects($this->any())
            ->method('ACLFieldAccess')
            ->willReturn(true);

        $this->assertSame($expected, $this->testBean->getAuditEnabledFieldDefinitions(false));
    }


    public function providerTestGetFirstAuditEnabledFieldDefinitions()
    {
        return [
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [
                    'test' => [
                        'name' => 'test_user_c',
                        'type' => 'relate',
                        'audited' => true,
                        'id_name' => 'account_id_c',
                        'module' => 'Accounts',
                    ],
                    'account_id_c' => [
                        'name' => 'test_user_c',
                        'type' => 'relate',
                        'audited' => true,
                        'id_name' => 'account_id_c',
                        'module' => 'Accounts',
                    ],
                ],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [
                    'test' => [
                        'name' => 'test_user_c',
                        'type' => 'name',
                        'audited' => true,
                        'id_name' => 'account_id_c',
                        'module' => 'Accounts',
                    ],
                ],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => '',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [],
                [],
            ],
        ];
    }

    public function providerTestGetSecondAuditEnabledFieldDefinitions()
    {
        return [
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => '',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [],
                [],
            ],
        ];
    }

    public function providerTestGetThirdAuditEnabledFieldDefinitions()
    {
        return [
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => '',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [],
                [],
            ],
        ];
    }

    public function providerTestGetFourthAuditEnabledFieldDefinitions()
    {
        return [
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [
                    'test' => [
                        'name' => 'test_user_c',
                        'type' => 'relate',
                        'audited' => true,
                        'id_name' => 'account_id_c',
                        'module' => 'Accounts',
                    ],
                ],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'relate',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => false,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'audited' => true,
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [
                    'test' => [
                        'name' => 'test_user_c',
                        'type' => 'name',
                        'audited' => true,
                        'id_name' => 'account_id_c',
                        'module' => 'Accounts',
                    ],
                ],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => 'account_id_c',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [
                    'name' => 'test_user_c',
                    'type' => 'name',
                    'id_name' => '',
                    'module' => 'Accounts',
                ],
                [],
            ],
            [
                [],
                [],
            ],
        ];
    }
}
