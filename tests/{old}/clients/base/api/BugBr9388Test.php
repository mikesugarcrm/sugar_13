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
 * @coversDefaultClass \RelateRecordApi
 */
class BugBr9388Test extends TestCase
{
    private $hooks;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        LogicHook::refreshHooks();
        $this->hooks = [
            ['Contacts', 'after_relationship_add', [1, 'Contacts::after_relationship_add', __FILE__, 'BugBr9388Hooks', 'exec']],
            ['Contacts', 'after_relationship_update', [1, 'Contacts::after_relationship_update', __FILE__, 'BugBr9388Hooks', 'exec']],
            ['Contacts', 'after_relationship_delete', [1, 'Contacts::after_relationship_delete', __FILE__, 'BugBr9388Hooks', 'exec']],
            ['Opportunities', 'after_relationship_add', [1, 'Opportunities::after_relationship_add', __FILE__, 'BugBr9388Hooks', 'exec']],
            ['Opportunities', 'after_relationship_update', [1, 'Opportunities::after_relationship_update', __FILE__, 'BugBr9388Hooks', 'exec']],
            ['Opportunities', 'after_relationship_delete', [1, 'Opportunities::after_relationship_delete', __FILE__, 'BugBr9388Hooks', 'exec']],
        ];
        foreach ($this->hooks as $hook) {
            call_user_func_array('check_logic_hook_file', $hook);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->hooks as $hook) {
            call_user_func_array('remove_logic_hook', $hook);
        }
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::createRelatedRecord
     * @return void
     * @throws SugarApiExceptionError
     */
    public function testCreateRelatedRecord()
    {
        $contact = SugarTestContactUtilities::createContact('', ['description' => 'not modified']);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();

        $response = $api->createRelatedRecord($service, [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'opportunities',
            'name' => 'opportunity of ' . $contact->name,
            'description' => 'not modified',
        ]);
        SugarTestOpportunityUtilities::setCreatedOpportunity([$response['related_record']['id']]);

        $this->assertEquals('not modified', $response['record']['description'], 'Primary bean was not reloaded');
    }

    /**
     * @covers ::createRelatedLink
     * @return void
     */
    public function testCreateRelatedLink()
    {
        $contact = SugarTestContactUtilities::createContact('', ['description' => 'not modified']);
        $opportunity = SugarTestOpportunityUtilities::createOpportunity('', null, ['description' => 'not modified']);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();

        $response = $api->createRelatedLink($service, [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'opportunities',
            'remote_id' => $opportunity->id,
        ]);

        $this->assertEquals('not modified', $response['record']['description'], 'Primary bean was not reloaded');
        $this->assertEquals('not modified', $response['related_record']['description'], 'Related bean was not reloaded');
    }

    /**
     * @covers ::createRelatedLinks
     * @return void
     * @throws SugarApiExceptionInvalidParameter
     * @throws SugarApiExceptionNotFound
     */
    public function testCreateRelatedLinks()
    {
        $contact = SugarTestContactUtilities::createContact('', ['description' => 'not modified']);
        $opportunity1 = SugarTestOpportunityUtilities::createOpportunity('', null, ['description' => 'not modified']);
        $opportunity2 = SugarTestOpportunityUtilities::createOpportunity('', null, ['description' => 'not modified']);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();

        $response = $api->createRelatedLinks($service, [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'opportunities',
            'ids' => [$opportunity1->id, $opportunity2->id],
        ]);

        $this->assertEquals('not modified', $response['record']['description'], 'Primary bean was not reloaded');

        $related = [];
        foreach ($response['related_records'] as $rel) {
            $related[$rel['id']] = $rel['description'];
        }
        $this->assertEquals('not modified', $related[$opportunity1->id], 'Related bean was not reloaded');
        $this->assertEquals('not modified', $related[$opportunity2->id], 'Related bean was not reloaded');
    }

    /**
     * @covers ::deleteRelatedLink
     * @return void
     * @throws SugarApiExceptionNotFound
     */
    public function testDeleteRelatedLink()
    {
        $contact = SugarTestContactUtilities::createContact('', ['description' => 'not modified']);
        $opportunity = SugarTestOpportunityUtilities::createOpportunity('', null, ['description' => 'not modified']);

        $this->assertTrue($contact->load_relationship('opportunities'), 'Relationship is not loaded');
        $contact->opportunities->add($opportunity);

        BeanFactory::unregisterBean('Contacts', $contact->id);
        BeanFactory::unregisterBean('Oppportunities', $opportunity->id);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->deleteRelatedLink($service, [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'opportunities',
            'remote_id' => $opportunity->id,
        ]);

        $this->assertEquals('not modified', $response['record']['description'], 'Primary bean was not reloaded');
        $this->assertEquals('not modified', $response['related_record']['description'], 'Related bean was not reloaded');
    }

    /**
     * @covers ::updateRelatedLink
     * @return void
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiExceptionNotFound
     */
    public function testUpdateRelatedLink()
    {
        $contact = SugarTestContactUtilities::createContact('', ['description' => 'not modified']);
        $opportunity = SugarTestOpportunityUtilities::createOpportunity('', null, ['description' => 'not modified']);

        $this->assertTrue($contact->load_relationship('opportunities'), 'Relationship is not loaded');
        $contact->opportunities->add($opportunity);

        BeanFactory::unregisterBean('Contacts', $contact->id);
        BeanFactory::unregisterBean('Oppportunities', $opportunity->id);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->updateRelatedLink($service, [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'opportunities',
            'remote_id' => $opportunity->id,
            'description' => 'updated description',
        ]);

        $this->assertEquals('not modified', $response['record']['description'], 'Primary bean was not reloaded');
        $this->assertEquals('updated description', $response['related_record']['description'], 'Related bean was not reloaded');
    }

    public function testCreateRelatedLinksFromRecordList()
    {
        $contact = SugarTestContactUtilities::createContact('', ['description' => 'not modified']);
        $opportunity1 = SugarTestOpportunityUtilities::createOpportunity('', null, ['description' => 'not modified']);
        $opportunity2 = SugarTestOpportunityUtilities::createOpportunity('', null, ['description' => 'not modified']);
        $recordListId = RecordListFactory::saveRecordList([$opportunity1->id, $opportunity2->id], 'Opportunities');

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();

        $response = $api->createRelatedLinksFromRecordList($service, [
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'opportunities',
            'remote_id' => $recordListId,
        ]);

        $this->assertEquals('not modified', $response['record']['description'], 'Primary bean was not reloaded');

        RecordListFactory::deleteRecordList($recordListId);
    }
}

class BugBr9388Hooks
{
    /**
     * @param SugarBean $bean
     * @param $event
     * @param $args
     * @return void
     */
    public static function exec($bean, $event, $args)
    {
        $bean->description = $bean->module_name . ' ' . $event . ' updated';
    }
}
