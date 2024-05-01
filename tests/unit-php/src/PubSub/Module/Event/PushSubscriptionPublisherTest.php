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

namespace Sugarcrm\SugarcrmTestsUnit\PubSub\Module\Event;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\PubSub\Module\Event\PushSubscriptionPublisher;

class PushSubscriptionPublisherTest extends TestCase
{
    public function allowedWebhooksProvider(): array
    {
        return [
            'empty url' => [
                '',
                false,
            ],
            'https://example.com' => [
                'https://example.com',
                false,
            ],
            'https://webhook.example.com' => [
                'https://webhook.example.com',
                false,
            ],
            'https://www.example.com' => [
                'https://www.example.com',
                false,
            ],
            'https://www.example.com/webhook' => [
                'https://www.example.com/webhook',
                false,
            ],
            'webhook on service.sugarcrm.com' => [
                'https://webhook.service.sugarcrm.com',
                true,
            ],
            'webhook on prod.sugar.build' => [
                'https://webhook.k8s-usw2.prod.sugar.build',
                true,
            ],
            'webhook on dev.sugar.build' => [
                'https://webhook.k8s-usw2.dev.sugar.build',
                true,
            ],
            'webhook with path' => [
                'https://apps.sugarcrm.com/webhook/sugar/core/',
                true,
            ],
            'case-insensitive url' => [
                'https://apps.service.SugarCRM.com/Webhook',
                true,
            ],
        ];
    }

    /**
     * @dataProvider allowedWebhooksProvider
     * @param string $url The webhook URL.
     * @param bool $expected The expected result.
     */
    public function testIsWebhookAllowed(string $url, bool $expected): void
    {
        $actual = PushSubscriptionPublisher::isWebhookAllowed($url);

        $this->assertSame($expected, $actual);
    }
}
