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

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSERelatedModule
 */
class PMSERelatedModulesTest extends TestCase
{
    /** @var  Account */
    private $account;

    private $accountOne;

    /** @var  Contact */
    private $contact;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, 1]);

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->lead_source = 'Cold Call';
        $this->contact->save();

        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact->id);

        $this->accountOne = SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    /**
     * Unit test class to cover getRelatedBeans method for a type of relationship 'all'.
     * @covers ::geRelatedBeans
     */
    public function testGetRelatedBeans()
    {
        // We are testing this object
        $r = ProcessManager\Factory::getPMSEObject('PMSERelatedModule');
        $res = $r->getRelatedBeans('Accounts', 'all');

        // Verify that we have the result we expect initially
        $this->assertArrayHasKey('result', $res);

        // Get one of each type to ensure that 'all' is working
        $o = $this->getRelatedModuleDef($res['result'], 'one');
        $m = $this->getRelatedModuleDef($res['result'], 'many');

        // Verify that we have at least one of each type
        $this->assertNotEmpty($o);
        $this->assertNotEmpty($m);

        // Verify that the label decorator was added
        $this->assertMatchesRegularExpression('/[*:1]/', $o['text']);
        $this->assertMatchesRegularExpression('/[*:M]/', $m['text']);
    }

    /**
     * @covers ::addRelatedRecord
     */
    public function testAddRelatedRecord()
    {
        $bugFields = [
            'assigned_user_id' => '1',
            'priority' => 'Urgent',
            'status' => 'New',
            'name' => 'New Bug ICE-717',
            'type' => 'Defect',
        ];

        $def = BeanFactory::newBean('pmse_BpmActivityDefinition');
        $def->act_params = '{
                       "module":"contacts",
                       "moduleLabel":"Contacts",
                       "filter":{
                           "expType":"MODULE",
                           "expSubtype":"DropDown",
                           "expLabel":"Contacts (Lead Source is equal to Cold Call)",
                           "expValue":"Cold Call",
                           "expOperator":"equals",
                           "expModule":"contacts",
                           "expField":"lead_source"
                        },
                       "chainedRelationship":{
                           "module":"bugs"
                        }
                }';

        $PMSERelatedModule = ProcessManager\Factory::getPMSEObject('PMSERelatedModule');
        $addedBeans = $PMSERelatedModule->addRelatedRecord($this->account, 'contacts', $bugFields, $def);

        // the Related Related Bug bean should be added to the Related Contact bean
        $this->contact->load_relationship('bugs');
        $bugBeans = $this->contact->bugs->getBeans();

        $this->assertNotEmpty($bugBeans[$addedBeans[0]->id]);

        // No Related To (module) is selected, a new record of the Related Module is added to the Target Module
        $contactFields = [
            'assigned_user_id' => '1',
            'last_name' => 'Doe',
        ];
        $def->act_params = '{"module":"contacts"}';
        $addedBeans = $PMSERelatedModule->addRelatedRecord($this->accountOne, 'contacts', $contactFields, $def);

        $this->accountOne->load_relationship('contacts');
        $contactBeans = $this->accountOne->contacts->getBeans();

        $this->assertNotEmpty($contactBeans[$addedBeans[0]->id]);
    }

    /**
     * Gets a single def from the resultant fetch, by relationship type
     * @param array $res The result of the collection
     * @param string $type The type of def to get
     * @return array
     */
    protected function getRelatedModuleDef($res, $type)
    {
        foreach ($res as $v) {
            if (isset($v['type']) && $v['type'] === $type) {
                return $v;
            }
        }

        return [];
    }
}
