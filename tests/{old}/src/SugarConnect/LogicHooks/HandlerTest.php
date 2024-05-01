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

namespace Sugarcrm\SugarcrmTests\SugarConnect\LogicHooks;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\SugarConnect\Client\Client;
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Locator;
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\ConfigurationInterface;
use Sugarcrm\Sugarcrm\SugarConnect\Event\Event;
use Sugarcrm\Sugarcrm\SugarConnect\LogicHooks\Handler;

class HandlerTest extends TestCase
{
    protected function setUp(): void
    {
        \SugarTestHelper::setUp('beanList');
        \SugarTestHelper::setUp('beanFiles');
        \SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        \SugarTestNoteUtilities::removeAllCreatedNotes();
        \SugarTestTaskUtilities::removeAllCreatedTasks();
        \SugarTestCallUtilities::removeAllCreatedCalls();
        \SugarTestMeetingUtilities::removeAllCreatedMeetings();
        \SugarTestLeadUtilities::removeAllCreatedLeads();
        \SugarTestContactUtilities::removeAllCreatedContacts();
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
        Locator::reset();
    }

    /**
     * The following events are published:
     *
     * 1. after_save: meeting
     * 2. after_save: call
     * 3. after_save: task
     * 4. after_relationship_add: meeting, lead
     * 5. after_relationship_add: meeting, contact
     * 6. after_relationship_delete: meeting, contact
     * 7. after_relationship_add: call, contact
     * 8. after_save: task (linking task to contact)
     * 9. after_relationship_delete: call, contact (during delete of contact)
     * 10. after_relationship_delete: meeting, lead (during delete of meeting)
     * 11. after_delete: meeting
     * 12. after_delete: call
     * 13. after_delete: task
     */
    public function testPublish_SugarConnectIsEnabled(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(13))->method('send');

        $config = $this->createMock(ConfigurationInterface::class);
        $config->expects($this->any())->method('isEnabled')->willReturn(true);
        $config->expects($this->any())->method('getClient')->willReturn($client);
        Locator::set($config);

        $this->performTest();
    }

    public function testPublish_SugarConnectIsDisabled(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('send');

        $config = $this->createMock(ConfigurationInterface::class);
        $config->expects($this->any())->method('isEnabled')->willReturn(false);
        $config->expects($this->never())->method('getClient')->willReturn($client);
        Locator::set($config);

        $this->performTest();
    }

    protected function performTest(): void
    {
        $account = \SugarTestAccountUtilities::createAccount();
        $contact = \SugarTestContactUtilities::createContact();
        $lead = \SugarTestLeadUtilities::createLead();
        $meeting = \SugarTestMeetingUtilities::createMeeting();
        $call = \SugarTestCallUtilities::createCall();
        $task = \SugarTestTaskUtilities::createTask();
        $note = \SugarTestNoteUtilities::createNote();

        $account->load_relationship('contacts');
        $account->contacts->add($contact);

        $meeting->load_relationship('leads');
        $meeting->leads->add($lead);

        $meeting->load_relationship('contacts');
        $meeting->contacts->add($contact);
        $meeting->contacts->delete($meeting, $contact->id);

        $call->load_relationship('contacts');
        $call->contacts->add($contact);

        // The contact_tasks relationship is a one-to-many relationship that
        // updates the tasks.contact_id field. Saving this relationship triggers
        // an after_save event on the task.
        $task->load_relationship('contacts');
        $task->contacts->add($contact);

        // The tasks_notes relationship is a one-to-many relationship with tasks
        // on the left side of the relationship. It updates the notes.parent_id
        // field. Saving this relationship triggers an after_save event on the
        // note but not the task.
        $task->load_relationship('notes');
        $task->notes->add($note);

        $contact->mark_deleted($contact->id);

        $meeting->mark_deleted($meeting->id);
        $call->mark_deleted($call->id);
        $task->mark_deleted($task->id);
    }
}
