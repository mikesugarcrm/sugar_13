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
 * Class KBUsefulnessRelationshipTest
 */
class KBUsefulnessRelationshipTest extends TestCase
{
    /**
     * Definition for relationship.
     * @var array
     */
    protected $def = [
        'name' => 'rel',
        'join_table' => 'jt',
        'true_relationship_type' => 'many-to-many',
        'primary_flag_column' => 'flag',
        'lhs_module' => 'Users',
        'rhs_module' => 'KBContents',
    ];

    /**
     * Check required where condition for relationship.
     * @param array $params params to pass into testing method.
     * @dataProvider getWhereParams
     */
    public function testGetWhere($params)
    {
        $rel = new KBUsefulnessRelationship($this->def);
        $rel->primaryOnly = true;
        $res = SugarTestReflection::callProtectedMethod($rel, 'getRoleWhere', $params);
        $this->assertEquals(' AND jt.flag = 1', $res);
        $rel->primaryOnly = false;
        $res = SugarTestReflection::callProtectedMethod($rel, 'getRoleWhere', $params);
        $this->assertNotEquals(' AND jt.flag = 1', $res);
    }

    /**
     * Data provider for test
     * @return array
     */
    public function getWhereParams()
    {
        return [
            [
                [
                    '',
                    true,
                    true,
                ],
            ],
            [
                [
                    'jt',
                    false,
                    true,
                ],
            ],
            [
                [
                    'jt',
                    true,
                    false,
                ],
            ],
            [
                [
                    'jt',
                    false,
                    false,
                ],
            ],
        ];
    }
}
