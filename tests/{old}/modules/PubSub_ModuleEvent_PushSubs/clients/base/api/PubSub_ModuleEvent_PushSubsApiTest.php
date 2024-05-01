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

class PubSub_ModuleEvent_PushSubsApiTest extends TestCase
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

    public function testCreateModuleEventPushSubscription(): void
    {
        $container = Container::getInstance();
        $config = $container->get(SugarConfig::class);
        $timedate = $container->get(TimeDate::class);

        $rest = SugarTestRestUtilities::getRestServiceMock($GLOBALS['current_user'], 'connections');
        $api = new PubSub_ModuleEvent_PushSubsApi();

        $args = [
            'module' => 'PubSub_ModuleEvent_PushSubs',
            'target_module' => 'Meetings',
            'token' => 'abcdef',
            'webhook_url' => 'https://webhook.service.sugarcrm.com',
        ];
        $resp = $api->createRecord($rest, $args);

        $sub = BeanFactory::retrieveBean('PubSub_ModuleEvent_PushSubs', $resp['id']);
        SugarTestPubSubUtilities::registerModuleEventPushSubscription($sub);

        $this->assertSame($config->get('site_url'), $resp['site_url'], 'wrong site_url');
        $this->assertNotEmpty($resp['id'], 'no id');
        $this->assertGreaterThan(
            $timedate->getNow()->getTimestamp(),
            $timedate->fromIso($resp['expiration_date'])->getTimestamp(),
            'expiration date not in the future'
        );
        $this->assertSame($args['target_module'], $resp['target_module'], 'wrong target module');
        $this->assertSame($args['token'], $sub->token, 'wrong token');
        $this->assertArrayNotHasKey('token', $resp, 'token not hidden');
        $this->assertSame('https://webhook.service.sugarcrm.com/', $resp['webhook_url'], 'wrong webhook url');
    }

    public function testUpdateModuleEventPushSubscription(): void
    {
        $container = Container::getInstance();
        $config = $container->get(SugarConfig::class);
        $timedate = $container->get(TimeDate::class);
        $now = $timedate->getNow();

        $sub1 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('-5 days')),
            'target_module' => 'Meetings',
            'token' => 'abcdef',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);

        $rest = SugarTestRestUtilities::getRestServiceMock($GLOBALS['current_user'], 'connections');
        $api = new PubSub_ModuleEvent_PushSubsApi();

        $args = [
            'module' => 'PubSub_ModuleEvent_PushSubs',
            'record' => $sub1->id,
            'target_module' => $sub1->target_module,
            'token' => 'uvwxyz',
            'webhook_url' => 'https://webhook.service.sugarcrm.com',
        ];
        $resp = $api->updateRecord($rest, $args);

        $sub2 = BeanFactory::retrieveBean('PubSub_ModuleEvent_PushSubs', $sub1->id);

        $this->assertSame($config->get('site_url'), $resp['site_url'], 'wrong site_url');
        $this->assertSame($args['record'], $resp['id'], 'wrong id');
        $this->assertGreaterThan(
            $timedate->getNow()->getTimestamp(),
            $timedate->fromIso($resp['expiration_date'])->getTimestamp(),
            'expiration date not in the future'
        );
        $this->assertSame($args['target_module'], $resp['target_module'], 'wrong target module');
        $this->assertSame($args['token'], $sub2->token, 'wrong token');
        $this->assertArrayNotHasKey('token', $resp, 'token not hidden');
        $this->assertSame($sub1->webhook_url, $resp['webhook_url'], 'wrong webhook url');
    }

    public function testDeleteModuleEventPushSubscription(): void
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

        $rest = SugarTestRestUtilities::getRestServiceMock($GLOBALS['current_user'], 'connections');
        $api = new PubSub_ModuleEvent_PushSubsApi();

        $args = [
            'module' => 'PubSub_ModuleEvent_PushSubs',
            'record' => $sub1->id,
        ];
        $resp = $api->deleteRecord($rest, $args);

        $sub2 = BeanFactory::retrieveBean('PubSub_ModuleEvent_PushSubs', $sub1->id, ['use_cache' => false]);

        $this->assertSame(['id' => $args['record']], $resp, 'wrong response');
        $this->assertNull($sub2, 'not deleted');
    }

    public function testUpsertModuleEventPushSubscription(): void
    {
        $container = Container::getInstance();
        $config = $container->get(SugarConfig::class);
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
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);

        $sub3 = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb((clone $now)->modify('+1 days')),
            'target_module' => 'Meetings',
            'token' => 'fizzbuzz',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);

        $rest = SugarTestRestUtilities::getRestServiceMock($GLOBALS['current_user'], 'connections');
        $api = new PubSub_ModuleEvent_PushSubsApi();

        $args = [
            'module' => 'PubSub_ModuleEvent_PushSubs',
            'target_module' => 'Meetings',
            'token' => 'uvwxyz',
            'webhook_url' => 'https://webhook.service.SugarCRM.com:443',
        ];
        $resp = $api->createRecord($rest, $args);

        $seed = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');
        $q = new SugarQuery();
        $q->from($seed);
        $q->where()->equals('target_module', 'Meetings');
        $q->where()->equals('webhook_url', 'https://webhook.service.sugarcrm.com/');
        $beans = $seed->fetchFromQuery($q);

        $this->assertCount(1, $beans, 'found duplicate subscriptions');

        $sub = array_shift($beans);

        $this->assertSame($config->get('site_url'), $resp['site_url'], 'wrong site_url');
        $this->assertSame($sub->id, $resp['id'], 'bean and response have different ids');
        $this->assertGreaterThan(
            $timedate->getNow()->getTimestamp(),
            $timedate->fromIso($resp['expiration_date'])->getTimestamp(),
            'expiration date not in the future'
        );
        $this->assertSame(
            $sub->expiration_date,
            $timedate->asDb($timedate->fromIso($resp['expiration_date'])),
            'bean and response have different expiration dates'
        );
        $this->assertSame($args['target_module'], $resp['target_module'], 'wrong target module');
        $this->assertSame(
            $sub->target_module,
            $resp['target_module'],
            'bean and response have different target modules'
        );
        $this->assertSame($args['token'], $sub->token, 'wrong token');
        $this->assertArrayNotHasKey('token', $resp, 'token not hidden');
        $this->assertSame('https://webhook.service.sugarcrm.com/', $resp['webhook_url'], 'wrong webhook url');
        $this->assertSame($sub->webhook_url, $resp['webhook_url'], 'bean and response have different webhook urls');
    }
}
