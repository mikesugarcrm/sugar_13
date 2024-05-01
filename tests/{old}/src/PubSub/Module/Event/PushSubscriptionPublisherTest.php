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

namespace Sugarcrm\SugarcrmTests\PubSub\Module\Event;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\PubSub\Client\PushClientInterface;
use Sugarcrm\Sugarcrm\PubSub\Module\Event\PushSubscriptionPublisher;
use Sugarcrm\SugarcrmTestsUnit\TestDependencyInjectionHelper;
use SugarTestAccountUtilities;
use SugarTestCallUtilities;
use SugarTestContactUtilities;
use SugarTestHelper;
use SugarTestLeadUtilities;
use SugarTestMeetingUtilities;
use SugarTestNoteUtilities;
use SugarTestTaskUtilities;
use SugarTestPubSubUtilities;
use TimeDate;

class PushSubscriptionPublisherTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::init();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        SugarTestPubSubUtilities::removeCreatedModuleEventPushSubscriptions();
        SugarTestNoteUtilities::removeAllCreatedNotes();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function publishEventProvider(): array
    {
        return [
            '1 active calls subscription and 1 inactive calls subscription' => [
                /**
                 * The following events are published:
                 *
                 * # create a call
                 * 1. after_save: call
                 * 2. after_relationship_add: call, current_user
                 *
                 * # invite the contact to the call
                 * 3. after_relationship_add: call, contact
                 *
                 * # delete the contact
                 * 4. after_relationship_delete: call, contact
                 *
                 * # delete the call
                 * 5. after_delete: call
                 */
                function (MockObject $client): InvocationMocker {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $now = $timedate->getNow();

                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('+4 days')),
                        'target_module' => 'Calls',
                        'token' => 'abcdef',
                        'webhook_url' => 'https://webhook.service.sugarcrm.com/',
                    ]);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('-9 days')),
                        'target_module' => 'Calls',
                        'token' => 'uvwxyz',
                        'webhook_url' => 'https://apps.service.sugarcrm.com/webhook/',
                    ]);

                    return $client->expects($this->exactly(5))->method('sendEvents');
                },
            ],
            '2 active tasks subscriptions' => [
                /**
                 * The following events are published:
                 *
                 * # create a task
                 * 1. after_save: task
                 * 2. after_save: task
                 *
                 * # link the contact to the task
                 * 3. after_relationship_add: task, contact
                 * 4. after_relationship_add: task, contact
                 * 5. after_save: task
                 * 6. after_save: task
                 *
                 * # add a note to the task
                 * 7. after_relationship_add: task, note
                 * 8. after_relationship_add: task, note
                 *
                 * # delete the contact
                 * 9. after_relationship_delete: task, contact
                 * 10. after_relationship_delete: task, contact
                 *
                 * # delete the task
                 * 11. after_delete: task
                 * 12. after_delete: task
                 */
                function (MockObject $client): InvocationMocker {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $now = $timedate->getNow();

                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('+5 days')),
                        'target_module' => 'Tasks',
                        'token' => 'abcdef',
                        'webhook_url' => 'https://webhook.service.sugarcrm.com/',
                    ]);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('+2 days')),
                        'target_module' => 'Tasks',
                        'token' => 'uvwxyz',
                        'webhook_url' => 'https://apps.service.sugarcrm.com/webhook/',
                    ]);

                    return $client->expects($this->exactly(12))->method('sendEvents');
                },
            ],
            '2 inactive meetings subscriptions' => [
                /**
                 * No events are published.
                 */
                function (MockObject $client): InvocationMocker {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $now = $timedate->getNow();

                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('-4 days')),
                        'target_module' => 'Meetings',
                        'token' => 'fizzbuzz',
                        'webhook_url' => 'https://webhook.service.sugarcrm.com/',
                    ]);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('-10 days')),
                        'target_module' => 'Meetings',
                        'token' => 'razzle',
                        'webhook_url' => 'https://webhook.k8s-usw2.stg.sugar.build/',
                    ]);

                    return $client->expects($this->never())->method('sendEvents');
                },
            ],
            '1 active meetings subscription and 1 active leads subscription' => [
                /**
                 * The following events are published:
                 *
                 * # create a lead
                 * 1. after_save: lead
                 *
                 * # create a meeting
                 * 2. after_save: meeting
                 * 3. after_relationship_add: meeting, current_user
                 *
                 * # invite the lead to the meeting
                 * 4. after_relationship_add: meeting, lead
                 * 5. after_relationship_add: lead, meeting
                 *
                 * # invite the contact to the meeting
                 * 6. after_relationship_add: meeting, contact
                 *
                 * # uninvite the contact from the meeting
                 * 7. after_relationship_delete: meeting, contact
                 *
                 * # delete the meeting
                 * 8. after_relationship_delete: lead, meeting
                 * 9. after_delete: meeting
                 */
                function (MockObject $client): InvocationMocker {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $now = $timedate->getNow();

                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('+4 days')),
                        'target_module' => 'Meetings',
                        'token' => 'abcdef',
                        'webhook_url' => 'https://webhook.service.sugarcrm.com/',
                    ]);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('+2 days')),
                        'target_module' => 'Leads',
                        'token' => 'abcdef',
                        'webhook_url' => 'https://webhook.service.sugarcrm.com/',
                    ]);

                    return $client->expects($this->exactly(9))->method('sendEvents');
                },
            ],
            '2 contacts subscriptions with unapproved webhooks' => [
                /**
                 * No events are published.
                 */
                function (MockObject $client): InvocationMocker {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $now = $timedate->getNow();

                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('+3 days')),
                        'target_module' => 'Contacts',
                        'token' => 'fizzbuzz',
                        'webhook_url' => 'https://webhook.example.com/',
                    ]);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb((clone $now)->modify('+5 days')),
                        'target_module' => 'Contacts',
                        'token' => 'razzle',
                        'webhook_url' => 'https://www.test.com/webhook/',
                    ]);

                    return $client->expects($this->never())->method('sendEvents');
                },
            ],
        ];
    }

    /**
     * @dataProvider publishEventProvider
     * @param callable $subFactory Lazily creates subscriptions.
     */
    public function testPublishEvent(callable $subFactory): void
    {
        // Mock the push client.
        $client = $this->createMock(PushClientInterface::class);
        TestDependencyInjectionHelper::resetContainer();
        TestDependencyInjectionHelper::setService(
            PushClientInterface::class,
            function (ContainerInterface $container) use ($client): PushClientInterface {
                return $client;
            }
        );

        // Create subscriptions and set client mock expectations.
        $client = $subFactory($client);
        $client->with(
            $this->logicalNot($this->isEmpty()),
            $this->callback(function (array $events): bool {
                $this->assertGreaterThan(0, count($events), 'no events');

                foreach ($events as $i => $event) {
                    $this->assertArrayHasKey('timestamp', $event, "event[{$i}] is missing 'timestamp'");
                    $this->assertArrayHasKey('site_url', $event, "event[{$i}] is missing 'site_url'");
                    $this->assertArrayHasKey('subscription_id', $event, "event[{$i}] is missing 'subscription_id'");
                    $this->assertArrayHasKey('token', $event, "event[{$i}] is missing 'token'");
                    $this->assertArrayHasKey('data', $event, "event[{$i}] is missing 'data'");
                    $this->assertArrayHasKey('module_name', $event['data'], "event[{$i}]['data'] is missing 'module_name'");
                    $this->assertArrayHasKey('id', $event['data'], "event[{$i}]['data'] is missing 'id'");
                    $this->assertArrayHasKey('change_type', $event['data'], "event[{$i}]['data'] is missing 'change_type'");
                    $this->assertArrayHasKey('arguments', $event['data'], "event[{$i}]['data'] is missing 'arguments'");
                }

                return true;
            })
        );

        // Create various records.
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $call = SugarTestCallUtilities::createCall();
        $task = SugarTestTaskUtilities::createTask();
        $note = SugarTestNoteUtilities::createNote();

        // Link the contact to the account.
        $account->load_relationship('contacts');
        $account->contacts->add($contact);

        // Invite the lead to the meeting.
        $meeting->load_relationship('leads');
        $meeting->leads->add($lead);

        // Invite the contact to the meeting.
        $meeting->load_relationship('contacts');
        $meeting->contacts->add($contact);

        // Uninvite the contact from the meeting.
        $meeting->contacts->delete($meeting, $contact->id);

        // Invite the contact to the call.
        $call->load_relationship('contacts');
        $call->contacts->add($contact);

        // Link the contact to the task.
        // Note: The contact_tasks relationship is a one-to-many relationship
        // that updates the tasks.contact_id field. Saving this relationship
        // triggers an after_save event on the task.
        $task->load_relationship('contacts');
        $task->contacts->add($contact);

        // Add a note to the task.
        // Note: The tasks_notes relationship is a one-to-many relationship with
        // tasks on the left side of the relationship. It updates the
        // notes.parent_id field. Saving this relationship triggers an
        // after_save event on the note but not the task.
        $task->load_relationship('notes');
        $task->notes->add($note);

        // Delete the contact.
        // Note: The contact is unlinked from the account and call, as well.
        $contact->mark_deleted($contact->id);

        // Delete the meeting.
        // Note: The lead is unlinked from the meeting, as well.
        $meeting->mark_deleted($meeting->id);

        // Delete the call.
        // Note: The contact was already unlinked from the call, so there are
        // not any additional after_relationship_delete events.
        $call->mark_deleted($call->id);

        // Delete the task.
        // Note: The contact was already unlinked from the task, so there are
        // not any additional after_relationship_delete events.
        $task->mark_deleted($task->id);
    }

    public function testDoNotPublishEvent(): void
    {
        $container = Container::getInstance();
        $timedate = $container->get(TimeDate::class);
        $now = $timedate->getNow();

        // Create a subscription to the PubSub_ModuleEvent_PushSubs module.
        SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('+6 days')),
            'target_module' => 'PubSub_ModuleEvent_PushSubs',
            'token' => 'abcdef',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);

        // Mock the push client.
        $client = $this->createMock(PushClientInterface::class);
        $client->expects($this->never())->method('sendEvents');
        TestDependencyInjectionHelper::resetContainer();
        TestDependencyInjectionHelper::setService(
            PushClientInterface::class,
            function (ContainerInterface $container) use ($client): PushClientInterface {
                return $client;
            }
        );

        // Create another subscription to the PubSub_ModuleEvent_PushSubs
        // module.
        // Note: The event should be muted because PubSub_ModuleEvent_PushSubs
        // is denied.
        SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('+7 days')),
            'target_module' => 'PubSub_ModuleEvent_PushSubs',
            'token' => 'fizzbuzz',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);
    }
}
