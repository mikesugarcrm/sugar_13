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
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class PubSub_ModuleEvent_PushSubTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::init();
        SugarTestHelper::setUp('current_user', [true, 1]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        SugarTestPubSubUtilities::removeCreatedModuleEventPushSubscriptions();
    }

    public function duplicatesProvider(): array
    {
        return [
            'no duplicates' => [
                function (PubSub_ModuleEvent_PushSub $sub): array {
                    return [];
                },
            ],
            'same subscription' => [
                function (PubSub_ModuleEvent_PushSub $sub): array {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $other = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);

                    // Give them the same ID.
                    $sub->id = $other->id;

                    return [];
                },
            ],
            'one duplicate' => [
                function (PubSub_ModuleEvent_PushSub $sub): array {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $duplicate = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);

                    return [$duplicate->id];
                },
            ],
            'two duplicates' => [
                function (PubSub_ModuleEvent_PushSub $sub): array {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $duplicate1 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+1 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);
                    $duplicate2 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);

                    return [$duplicate1->id, $duplicate2->id];
                },
            ],
            'three duplicates' => [
                function (PubSub_ModuleEvent_PushSub $sub): array {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $duplicate1 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('-4 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);
                    $duplicate2 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+1 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);
                    $duplicate3 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);

                    return [$duplicate1->id, $duplicate2->id, $duplicate3->id];
                },
            ],
        ];
    }

    public function notDuplicatesProvider(): array
    {
        return [
            'same module but different webhook' => [
                function (PubSub_ModuleEvent_PushSub $sub): void {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => 'https://apps.service.sugarcrm.com/webhook/',
                    ]);
                },
            ],
            'same webhook but different module' => [
                function (PubSub_ModuleEvent_PushSub $sub): void {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => 'Accounts',
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);
                },
            ],
            'different module and webhook' => [
                function (PubSub_ModuleEvent_PushSub $sub): void {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => 'Accounts',
                        'token' => $sub->token,
                        'webhook_url' => 'https://apps.service.sugarcrm.com/webhook/',
                    ]);
                },
            ],
            'same module and webhook but also same id' => [
                function (PubSub_ModuleEvent_PushSub $sub): void {
                    $container = Container::getInstance();
                    $timedate = $container->get(TimeDate::class);
                    $other = SugarTestPubSubUtilities::createModuleEventPushSubscription([
                        'expiration_date' => $timedate->asDb($timedate->getNow()->modify('+3 days')),
                        'target_module' => $sub->target_module,
                        'token' => $sub->token,
                        'webhook_url' => $sub->webhook_url,
                    ]);

                    // Give them the same ID.
                    $sub->id = $other->id;
                },
            ],
        ];
    }

    public function testFindActiveSubscriptionsByModule(): void
    {
        $container = Container::getInstance();
        $timedate = $container->get(TimeDate::class);
        $now = $timedate->getNow();

        $sub1 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('+5 days')),
            'target_module' => 'Meetings',
            'token' => 'abcdef',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);

        $sub2 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('+3 days')),
            'target_module' => 'Meetings',
            'token' => 'abbabba',
            'webhook_url' => 'https://apps.service.sugarcrm.com/webhook/',
        ]);

        $sub3 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('-1 days')),
            'target_module' => 'Meetings',
            'token' => 'fizzbuzz',
            'webhook_url' => 'https://apps.service.sugarcrm.com/webhook/',
        ]);

        $sub4 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('+7 days')),
            'target_module' => 'Contacts',
            'token' => 'uvwxyz',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);

        $sub5 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('-4 days')),
            'target_module' => 'Accounts',
            'token' => 'foobar',
            'webhook_url' => 'https://apps.service.sugarcrm.com/webhook/',
        ]);

        $subs = PubSub_ModuleEvent_PushSub::findActiveSubscriptionsByModule('Meetings');

        $this->assertCount(2, $subs, 'wrong meetings subscriptions count');
        $this->assertEqualsCanonicalizing([$sub1->id, $sub2->id], array_keys($subs), 'wrong meetings subscriptions');

        $subs = PubSub_ModuleEvent_PushSub::findActiveSubscriptionsByModule('Contacts');

        $this->assertCount(1, $subs, 'wrong contacts subscriptions count');
        $this->assertEqualsCanonicalizing([$sub4->id], array_keys($subs), 'wrong contacts subscriptions');

        $subs = PubSub_ModuleEvent_PushSub::findActiveSubscriptionsByModule('Accounts');

        $this->assertCount(0, $subs, 'wrong accounts subscriptions count');

        $subs = PubSub_ModuleEvent_PushSub::findActiveSubscriptionsByModule('Calls');

        $this->assertCount(0, $subs, 'wrong calls subscriptions count');
    }

    /**
     * @dataProvider duplicatesProvider
     * @param callable $subFactory Lazily creates subscriptions and returns
     *                             their IDs.
     */
    public function testFindDuplicates(callable $subFactory): void
    {
        $container = Container::getInstance();
        $timedate = $container->get(TimeDate::class);

        $sub = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');
        $sub->expiration_date = $timedate->asDb($timedate->getNow()->modify('+7 days'));
        $sub->target_module = 'Meetings';
        $sub->token = 'abcdef';
        $sub->webhook_url = 'https://webhook.service.sugarcrm.com';

        // Create the duplicate subscriptions.
        $ids = $subFactory($sub);

        $duplicates = $sub->findDuplicates();

        $this->assertEqualsCanonicalizing($ids, array_keys($duplicates['records']), 'wrong duplicates');
    }

    /**
     * @dataProvider notDuplicatesProvider
     * @param callable $subFactory Lazily creates a subscription.
     */
    public function testFindDuplicates_NoDuplicatesFound(callable $subFactory): void
    {
        $container = Container::getInstance();
        $timedate = $container->get(TimeDate::class);

        $sub = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');
        $sub->expiration_date = $timedate->asDb($timedate->getNow()->modify('+7 days'));
        $sub->target_module = 'Meetings';
        $sub->token = 'abcdef';
        $sub->webhook_url = 'https://webhook.service.sugarcrm.com';

        // Create the other subscription.
        $subFactory($sub);

        $duplicates = $sub->findDuplicates();

        $this->assertCount(0, $duplicates['records'], 'found duplicates');
    }

    /**
     * @dataProvider duplicatesProvider
     * @param callable $subFactory Lazily creates subscriptions and returns
     *                             their IDs.
     */
    public function testDeleteDuplicates(callable $subFactory): void
    {
        $container = Container::getInstance();
        $timedate = $container->get(TimeDate::class);

        $sub = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');
        $sub->expiration_date = $timedate->asDb($timedate->getNow()->modify('+7 days'));
        $sub->target_module = 'Meetings';
        $sub->token = 'abcdef';
        $sub->webhook_url = 'https://webhook.service.sugarcrm.com';

        // Create the duplicate subscriptions.
        $ids = $subFactory($sub);

        $numDeleted = $sub->deleteDuplicates();
        $duplicates = $sub->findDuplicates();

        $this->assertCount($numDeleted, $ids, 'wrong deleted count');
        $this->assertCount(0, $duplicates['records'], 'found duplicate subscriptions');
    }

    /**
     * @dataProvider notDuplicatesProvider
     * @param callable $subFactory Lazily creates a subscription.
     */
    public function testDeleteDuplicates_NoDuplicatesFound(callable $subFactory): void
    {
        $container = Container::getInstance();
        $timedate = $container->get(TimeDate::class);

        $sub = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');
        $sub->expiration_date = $timedate->asDb($timedate->getNow()->modify('+7 days'));
        $sub->target_module = 'Meetings';
        $sub->token = 'abcdef';
        $sub->webhook_url = 'https://webhook.service.sugarcrm.com';

        // Create the other subscription.
        $subFactory($sub);

        $numDeleted = $sub->deleteDuplicates();
        $duplicates = $sub->findDuplicates();

        $this->assertSame(0, $numDeleted, 'wrong deleted count');
        $this->assertCount(0, $duplicates['records'], 'found duplicate subscriptions');
    }
}
