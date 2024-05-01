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
 * Bug #56425
 * see duplicate modules name in Report's Related Modules box
 *
 * @author mgusev@sugarcrm.com
 * @ticked 56425
 * @ticket 42169
 */
class Bug56425Test extends TestCase
{
    /**
     * Test asserts genereated labels for activities
     *
     * @param array $definition
     * @param array $expected
     * @dataProvider getDefinitions
     * @group 56425
     * @group 42169
     * @return void
     */
    public function testBuildLabels($definition, $expected)
    {
        ActivitiesRelationship56425::reset($definition['lhs_module']);
        $relationship = new ActivitiesRelationship56425($definition);
        $labels = $relationship->buildLabels();
        foreach ($labels as $label) {
            $this->assertArrayHasKey($label['module'], $expected, 'Incorrect label was generated');
            $this->assertEquals($expected[$label['module']], $label['display_label'], 'Labels are incorrect');
            unset($expected[$label['module']]);
        }
        $this->assertEmpty($expected, 'Not all labels were generated');
    }

    /**
     * Method returns definition for relationship & expected result
     *
     * @return array
     */
    public function getDefinitions()
    {
        return [
            [
                [
                    'rhs_label' => 'Activities',
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship',
                ],
                [
                    'Contacts' => 'Users',
                    'Users' => 'Contacts',
                ],
            ],
            [
                [
                    'rhs_label' => 'Activities 123',
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship',
                ],
                [
                    'Contacts' => 'Users',
                    'Users' => 'Contacts',
                ],
            ],
            [
                [
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship',
                ],
                [
                    'Contacts' => 'Users',
                    'Users' => 'Contacts',
                ],
            ],
            [
                [
                    'lhs_module' => 'lhs_module',
                    'lhs_label' => 'lhs_label',
                    'rhs_module' => 'rhs_module',
                    'rhs_label' => 'rhs_label',
                ],
                [
                    'lhs_module' => 'rhs_module',
                    'rhs_module' => 'lhs_module',
                ],
            ],
        ];
    }
}

class ActivitiesRelationship56425 extends ActivitiesRelationship
{
    public static function reset($module)
    {
        self::$labelsAdded = [
            $module => true,
        ];
    }
}
