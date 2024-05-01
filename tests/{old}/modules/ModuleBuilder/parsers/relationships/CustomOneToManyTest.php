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
 * Test building a one to many relationship
 *
 * @ticket 59889
 */
class CustomOneToManyTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }


    public function testSelfReferencing()
    {
        $definition = [
            'rhs_label' => 'Opportunities',
            'lhs_label' => 'Opportunities',
            'rhs_subpanel' => 'default',
            'lhs_module' => 'Opportunities',
            'rhs_module' => 'Opportunities',
            'relationship_type' => 'one-to-many',
            'readonly' => false,
            'deleted' => false,
            'relationship_only' => false,
            'for_activities' => false,
            'is_custom' => false,
            'from_studio' => false,
            'relationship_name' => 'opportunities_opportunities_2',
        ];

        $testRel = new OneToManyRelationship($definition);
        $vardefs = $testRel->buildVardefs();
        $subPanel = $testRel->buildSubpanelDefinitions();
        $relData = $testRel->buildRelationshipMetaData();

        // print_r($vardefs);
        $this->assertEquals(4, safeCount($vardefs['Opportunities']), "Doesn't have all four entries (link, link2, id, name)");

        $sortedDefs = [];
        foreach ($vardefs['Opportunities'] as $def) {
            if ($def['type'] == 'link') {
                if (isset($def['side']) && $def['side'] == 'right') {
                    $sortedDefs['link2'] = $def;
                } else {
                    $sortedDefs['link'] = $def;
                }
            } else {
                if (isset($def['rname']) && $def['rname'] == 'id') {
                    $sortedDefs['id'] = $def;
                } else {
                    $sortedDefs['name'] = $def;
                }
            }
        }

        $this->assertEquals(4, count($sortedDefs), "Couldn't sort out the four entries only found: " . print_r(array_keys($sortedDefs), true));

        $this->assertEquals($sortedDefs['id']['name'], $sortedDefs['id']['id_name'], "ID's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'], $sortedDefs['link2']['id_name'], "Link2's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'], $sortedDefs['name']['id_name'], "Name's id_name is wrong!");
        $this->assertNotEquals($sortedDefs['id']['name'], $sortedDefs['link']['id_name'], "Link's id_name is the same as the right side id_name");

        // print_r($subPanel);
        $this->assertEquals($sortedDefs['link']['name'], $subPanel['Opportunities'][0]['get_subpanel_data'], 'Subpanel is using the incorrect link');
        // print_r($relData);
    }

    public function testNormal()
    {
        $definition = [
            'rhs_label' => 'Opportunities',
            'lhs_label' => 'Person Module',
            'rhs_subpanel' => 'default',
            'lhs_module' => 'Opportunities',
            'rhs_module' => 'bugs',
            'relationship_type' => 'one-to-many',
            'readonly' => false,
            'deleted' => false,
            'relationship_only' => false,
            'for_activities' => false,
            'is_custom' => false,
            'from_studio' => false,
            'relationship_name' => 'opportunities_bugs',
        ];

        $testRel = new OneToManyRelationship($definition);
        $vardefs = $testRel->buildVardefs();
        $subPanel = $testRel->buildSubpanelDefinitions();
        $relData = $testRel->buildRelationshipMetaData();

        // print_r($vardefs);
        $this->assertEquals(3, safeCount($vardefs['bugs']), "Doesn't have the three right entries (link2, id, name)");
        $this->assertEquals(1, safeCount($vardefs['Opportunities']), "Doesn't have just one left entry)");

        $sortedDefs = [];
        foreach ($vardefs['bugs'] as $def) {
            if ($def['type'] == 'link') {
                if (isset($def['side']) && $def['side'] == 'right') {
                    $sortedDefs['link2'] = $def;
                } else {
                    $this->assertFalse(true, 'The right hand side has a left-handed link field.');
                }
            } else {
                if (isset($def['rname']) && $def['rname'] == 'id') {
                    $sortedDefs['id'] = $def;
                } else {
                    $sortedDefs['name'] = $def;
                }
            }
        }
        $sortedDefs['link'] = $vardefs['Opportunities'][0];

        $this->assertEquals(4, count($sortedDefs), "Couldn't sort out the four entries only found: " . print_r(array_keys($sortedDefs), true));

        $this->assertEquals($sortedDefs['id']['name'], $sortedDefs['id']['id_name'], "ID's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'], $sortedDefs['link2']['id_name'], "Link2's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'], $sortedDefs['name']['id_name'], "Name's id_name is wrong!");

        // print_r($subPanel);
        $this->assertEquals($sortedDefs['link']['name'], $subPanel['Opportunities'][0]['get_subpanel_data'], 'Subpanel is using the incorrect link');
        // print_r($relData);
    }
}
