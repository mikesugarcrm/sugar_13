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
 * @ticket 50422
 */
class Bug50422Test extends TestCase
{
    /** @var  Call */
    private $call;

    /** @var  Contact */
    private $contact;

    /** @var DeployedRelationships */
    private $relationships;

    /** @var OneToManyRelationship */
    private $relationship;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');

        $this->relationships = new DeployedRelationships('Contacts');
        $definition = [
            'lhs_module' => 'Contacts',
            'relationship_type' => 'one-to-many',
            'rhs_module' => 'Calls',
        ];

        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', ['Contacts', 'Calls']);

        $this->call = SugarTestCallUtilities::createCall();
        $contact = $this->contact = SugarTestContactUtilities::createContact();
        $contact->salutation = 'Mr.';
        $contact->first_name = 'Bug50422Fn';
        $contact->last_name = 'Bug50422Ln';
        $contact->save();

        $relationshipName = $this->relationship->getName();
        $this->call->load_relationship($relationshipName);
        $this->call->$relationshipName->add($this->contact);
    }

    protected function tearDown(): void
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();

        if ($this->relationship && $this->relationships) {
            $this->relationships->delete($this->relationship->getName());
            $this->relationships->save();
        }

        SugarTestHelper::tearDown();
    }

    public function testRelateFullNameFormat()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 's l, f');

        $call = $this->call;
        $relationshipName = $this->relationship->getName();
        $relateFieldName = $relationshipName . '_name';

        $lvd = new ListViewData();
        $lvd->listviewName = $call->module_name;
        $response = $lvd->getListViewData(
            $call,
            'calls.id = ' . $call->db->quoted($call->id),
            -1,
            -1,
            [$relateFieldName]
        );

        $this->assertArrayHasKey('data', $response, 'Response doesn\'t contain data');
        $this->assertIsArray($response['data'], 'Response data is not array');
        $this->assertEquals(1, count($response['data']), 'Response data should contain exactly 1 item');

        $relateFieldName = strtoupper($relateFieldName);
        $row = array_shift($response['data']);
        $this->assertIsArray($row, 'Data row is not array');
        $this->assertArrayHasKey($relateFieldName, $row, 'Row doesn\'t contain contact name');
        $this->assertEquals('Mr. Bug50422Ln, Bug50422Fn', $row[$relateFieldName], 'Full name format is incorrect');
    }
}
