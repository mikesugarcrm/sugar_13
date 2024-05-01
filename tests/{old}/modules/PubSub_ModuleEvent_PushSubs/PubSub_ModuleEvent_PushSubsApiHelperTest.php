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

class PubSub_ModuleEvent_PushSubsApiHelperTest extends TestCase
{
    private static PubSub_ModuleEvent_PushSubsApiHelper $helper;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::init();
        SugarTestHelper::setUp('current_user', [true, 1]);

        $rest = SugarTestRestUtilities::getRestServiceMock($GLOBALS['current_user'], 'connections');
        static::$helper = new PubSub_ModuleEvent_PushSubsApiHelper($rest);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestPubSubUtilities::removeCreatedModuleEventPushSubscriptions();
        SugarTestHelper::tearDown();
    }

    public function notAllowedModulesProvider(): array
    {
        return [
            'empty string' => [
                '',
            ],
            'PubSub_ModuleEvent_PushSubs' => [
                'PubSub_ModuleEvent_PushSubs',
            ],
        ];
    }

    public function notAllowedWebhooksProvider(): array
    {
        return [
            'empty url' => [
                '',
            ],
            'https://example.com' => [
                'https://example.com',
            ],
            'https://webhook.example.com' => [
                'https://webhook.example.com',
            ],
            'https://www.example.com' => [
                'https://www.example.com',
            ],
            'https://www.example.com/webhook' => [
                'https://www.example.com/webhook',
            ],
        ];
    }

    public function testFormatForApi(): void
    {
        $container = Container::getInstance();
        $config = $container->get(SugarConfig::class);
        $timedate = $container->get(TimeDate::class);
        $now = $timedate->getNow();

        $sub = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'description' => 'blah blah blah',
            'expiration_date' => $timedate->asDb((clone $now)->modify('+5 days')),
            'target_module' => 'Meetings',
            'token' => 'abcdef',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ]);

        $fieldList = [
            'description',
            'expiration_date',
            'id',
            'target_module',
            'token',
            'webhook_url',
        ];
        $data = static::$helper->formatForApi($sub, $fieldList);

        $this->assertSame($config->get('site_url'), $data['site_url'], 'wrong site_url');
        $this->assertSame($sub->id, $data['id'], 'wrong id');
        $this->assertArrayNotHasKey('description', $data, 'description not hidden');
        $this->assertSame(
            $timedate->fromDb($sub->expiration_date)->getTimestamp(),
            $timedate->fromIso($data['expiration_date'])->getTimestamp(),
            'wrong expiration date'
        );
        $this->assertSame($sub->target_module, $data['target_module'], 'wrong target module');
        $this->assertArrayNotHasKey('token', $data, 'token not hidden');
        $this->assertSame($sub->webhook_url, $data['webhook_url'], 'wrong webhook url');
    }

    public function testPopulateFromApi(): void
    {
        $container = Container::getInstance();
        $timedate = $container->get(TimeDate::class);
        $now = $timedate->getNow();
        $expiresIn1Day = (clone $now)->modify('+1 days');
        $expiresIn3Days = (clone $now)->modify('+3 days');
        $expiresIn7Days = (clone $now)->modify('+7 days');

        $sub = SugarTestPubSubUtilities::createModuleEventPushSubscription([
            'expiration_date' => $timedate->asDb($expiresIn3Days),
            'target_module' => 'Meetings',
            'token' => 'abcdef',
            'webhook_url' => 'https://webhook.k8s-usw2.dev.sugar.build/',
        ]);

        $data = [
            'module' => 'PubSub_ModuleEvent_PushSubs',
            'id' => $sub->id,
            // Try to shorten the expiration date.
            'expiration_date' => $timedate->asIso($expiresIn1Day),
            'target_module' => $sub->target_module,
            // Change the token.
            'token' => 'uvwxyz',
            // The same url but not normalized.
            'webhook_url' => 'https://webhook.k8s-USW2.dev.Sugar.build:443',
        ];
        static::$helper->populateFromApi($sub, $data);

        $this->assertSame($data['id'], $sub->id, 'wrong id');
        $this->assertGreaterThanOrEqual(
            $expiresIn7Days->getTimestamp(),
            $timedate->fromDb($sub->expiration_date)->getTimestamp(),
            'expiration date not extended'
        );
        $this->assertSame('Meetings', $sub->target_module, 'wrong target module');
        $this->assertSame($data['token'], $sub->token, 'wrong token');
        $this->assertSame('https://webhook.k8s-usw2.dev.sugar.build/', $sub->webhook_url, 'wrong webhook url');
    }

    /**
     * @dataProvider notAllowedModulesProvider
     * @param string $moduleName The module name.
     */
    public function testPopulateFromApi_ModuleNotAllowed(string $moduleName): void
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);

        $sub = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');
        $data = [
            'module' => 'PubSub_ModuleEvent_PushSubs',
            'target_module' => $moduleName,
            'token' => 'abcdef',
            'webhook_url' => 'https://webhook.service.sugarcrm.com/',
        ];
        static::$helper->populateFromApi($sub, $data);
    }

    /**
     * @dataProvider notAllowedWebhooksProvider
     * @param string $url The webhook URL.
     */
    public function testPopulateFromApi_WebhookNotAllowed(string $url): void
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);

        $sub = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');
        $data = [
            'module' => 'PubSub_ModuleEvent_PushSubs',
            'target_module' => 'Contacts',
            'token' => 'abcdef',
            'webhook_url' => $url,
        ];
        static::$helper->populateFromApi($sub, $data);
    }
}
