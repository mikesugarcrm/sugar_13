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
 * @coversDefaultClass \SugarACLProduct
 */
class SugarACLProductTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Set up a non-admin user
        \SugarTestHelper::setUp('current_user', [true, false]);

        // Set up the Product Templates ACLs
        $aclData = [
            'module' => [
                'create' => [
                    'aclaccess' => ACL_ALLOW_DISABLED,
                ],
                'edit' => [
                    'aclaccess' => ACL_ALLOW_DISABLED,
                ],
                'list' => [
                    'aclaccess' => ACL_ALLOW_ALL,
                ],
                'view' => [
                    'aclaccess' => ACL_ALLOW_ALL,
                ],
                'access' => [
                    'aclaccess' => ACL_ALLOW_ALL,
                ],
                'team_security' => [
                    'aclaccess' => ACL_ALLOW_ALL,
                ],
                'field' => [
                    'aclaccess' => ACL_ALLOW_ALL,
                ],
            ],
        ];
        ACLAction::setACLData($GLOBALS['current_user']->id, 'ProductTemplates', $aclData);
    }

    public function checkAccessProvider()
    {
        return [
            ['Manufacturers', 'create', false],
            ['Manufacturers', 'edit', false],
            ['Manufacturers', 'list', true],
            ['Manufacturers', 'view', true],
            ['Manufacturers', 'team_security', true],
            ['Manufacturers', 'access', true],
            ['Manufacturers', 'field', true],
            ['ProductTypes', 'create', false],
            ['ProductTypes', 'edit', false],
            ['ProductTypes', 'list', true],
            ['ProductTypes', 'view', true],
            ['ProductTypes', 'team_security', true],
            ['ProductTypes', 'access', true],
            ['ProductTypes', 'field', true],
            ['ProductCategories', 'create', false],
            ['ProductCategories', 'edit', false],
            ['ProductCategories', 'list', true],
            ['ProductCategories', 'view', true],
            ['ProductCategories', 'team_security', true],
            ['ProductCategories', 'access', true],
            ['ProductCategories', 'field', true],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkAccessProvider
     */
    public function testCheckAccess($module, $view, $result)
    {
        $acl = $this->getMockBuilder('SugarACLProduct')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame($result, $acl->checkAccess($module, $view, []));
    }
}
