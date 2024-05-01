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

namespace Sugarcrm\SugarcrmTestsUnit\PubSub\Client\Batch;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Clock\FakeClock;
use Sugarcrm\Sugarcrm\Clock\Timer;
use Sugarcrm\Sugarcrm\PubSub\Buffer\InMemory\PushSubscriptionBuffer;
use Sugarcrm\Sugarcrm\PubSub\Client\Batch\PushClient;
use Sugarcrm\Sugarcrm\PubSub\Client\PushClientInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\PubSub\Client\Batch\PushClient
 */
class PushClientTest extends TestCase
{
    public function bufferCapacityProvider(): array
    {
        return [
            'no buffering' => [0, 5, 0],
            'buffer capacity of 1' => [1, 5, 0],
            'buffer capacity of 2' => [2, 3, 1],
            'buffer capacity of 3' => [3, 2, 2],
            'buffer capacity of 4' => [4, 2, 1],
            'buffer capacity of 5' => [5, 2, 0],
            'buffer capacity of 6' => [6, 0, 2],
        ];
    }

    public function bufferTimeoutProvider(): array
    {
        return [
            'no buffering' => [0, 1, 1, 1, 1, 1, 0],
            'buffer timeout of 1' => [1, 0, 1, 0, 2, 0, 1],
            'buffer timeout of 2' => [2, 0, 0, 2, 0, 0, 2],
            'buffer timeout of 3' => [3, 0, 0, 0, 2, 0, 1],
            'buffer timeout of 4' => [4, 0, 0, 0, 0, 2, 0],
            'buffer timeout of 5' => [5, 0, 0, 0, 0, 0, 2],
            'buffer timeout of 6' => [6, 0, 0, 0, 0, 0, 2],
        ];
    }

    /**
     * @covers ::sendEvents
     * @covers ::flushEvents
     * @dataProvider bufferCapacityProvider
     *
     * @param int $bufferCapacity The buffer size.
     * @param int $reqsBeforeFlush The number of requests sent due to reaching
     *                             capacity.
     * @param int $reqsAfterFlush The number of requests sent due to flushing.
     */
    public function testBufferCapacity(int $bufferCapacity, int $reqsBeforeFlush, int $reqsAfterFlush)
    {
        $reqsCount = 0;
        $reqsAfterFlush = $reqsBeforeFlush + $reqsAfterFlush;

        $mockClient = $this->createMock(PushClientInterface::class);
        $mockClient->method('sendEvents')->willReturnCallback(
            function (string $url, array $events) use (&$reqsCount) {
                $reqsCount++;
            }
        );

        $clock = new FakeClock();
        $timer = new Timer();
        $timer->setClock($clock);

        $buffer = new PushSubscriptionBuffer();
        $buffer->setCapacity($bufferCapacity);
        $buffer->setTimeout(30);
        $buffer->setTimer($timer);

        $batchClient = new PushClient($mockClient);
        $batchClient->setBuffer($buffer);

        $webhook1 = 'https://webhook.service.sugarcrm.com/';
        $webhook2 = 'https://www.test.com/webhook';

        $batchClient->sendEvents(
            $webhook1,
            [
                [
                    'timestamp' => '2023-02-14T10:06:17Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Contacts',
                        'id' => 'b93a85a6-d36b-41f4-928e-2c4d710ea5f2',
                        'change_type' => 'after_relationship_add',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $batchClient->sendEvents(
            $webhook1,
            [
                [
                    'timestamp' => '2023-02-14T10:06:18Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Calls',
                        'id' => 'b6df61f0-ca62-4c62-96a6-c4c2a7ea77c6',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $batchClient->sendEvents(
            $webhook2,
            [
                [
                    'timestamp' => '2023-02-14T10:06:19Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Calls',
                        'id' => 'b6df61f0-ca62-4c62-96a6-c4c2a7ea77c6',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $batchClient->sendEvents(
            $webhook1,
            [
                [
                    'timestamp' => '2023-02-14T10:06:20Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Contacts',
                        'id' => 'b93a85a6-d36b-41f4-928e-2c4d710ea5f2',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $batchClient->sendEvents(
            $webhook2,
            [
                [
                    'timestamp' => '2023-02-14T10:06:21Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Contacts',
                        'id' => 'b93a85a6-d36b-41f4-928e-2c4d710ea5f2',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $this->assertSame($reqsBeforeFlush, $reqsCount, 'buffer has not been flushed');
        $batchClient->flushEvents();
        $this->assertSame($reqsAfterFlush, $reqsCount, 'buffer has been flushed');
    }

    /**
     * @covers ::sendEvents
     * @covers ::flushEvents
     * @dataProvider bufferTimeoutProvider
     *
     * @param int $bufferTimeout The buffer timeout.
     * @param int $reqsAfter1Second The number of requests sent after 1 second
     *                              has passed.
     * @param int $reqsAfter2Seconds The number of requests sent after 2 seconds
     *                               have passed.
     * @param int $reqsAfter3Seconds The number of requests sent after 3 seconds
     *                               have passed.
     * @param int $reqsAfter4Seconds The number of requests sent after 4 seconds
     *                               have passed.
     * @param int $reqsAfter5Seconds The number of requests sent after 5 seconds
     *                               have passed.
     * @param int $reqsAfterFlush The number of requests sent due to flushing.
     */
    public function testBufferTimeout(
        int $bufferTimeout,
        int $reqsAfter1Second,
        int $reqsAfter2Seconds,
        int $reqsAfter3Seconds,
        int $reqsAfter4Seconds,
        int $reqsAfter5Seconds,
        int $reqsAfterFlush
    ) {

        $reqsCount = 0;
        $reqsAfter2Seconds = $reqsAfter1Second + $reqsAfter2Seconds;
        $reqsAfter3Seconds = $reqsAfter2Seconds + $reqsAfter3Seconds;
        $reqsAfter4Seconds = $reqsAfter3Seconds + $reqsAfter4Seconds;
        $reqsAfter5Seconds = $reqsAfter4Seconds + $reqsAfter5Seconds;
        $reqsAfterFlush = $reqsAfter5Seconds + $reqsAfterFlush;

        $mockClient = $this->createMock(PushClientInterface::class);
        $mockClient->method('sendEvents')->willReturnCallback(
            function (string $url, array $events) use (&$reqsCount) {
                $reqsCount++;
            }
        );

        $clock = new FakeClock();
        $timer = new Timer();
        $timer->setClock($clock);

        $buffer = new PushSubscriptionBuffer();
        $buffer->setCapacity(20);
        $buffer->setTimeout($bufferTimeout);
        $buffer->setTimer($timer);

        $batchClient = new PushClient($mockClient);
        $batchClient->setBuffer($buffer);

        $webhook1 = 'https://webhook.service.sugarcrm.com/';
        $webhook2 = 'https://www.test.com/webhook';

        $batchClient->sendEvents(
            $webhook1,
            [
                [
                    'timestamp' => '2023-02-14T10:06:17Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Contacts',
                        'id' => 'b93a85a6-d36b-41f4-928e-2c4d710ea5f2',
                        'change_type' => 'after_relationship_add',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $clock->sleep(1);
        $this->assertSame($reqsAfter1Second, $reqsCount, 'time has advanced 1 second');

        $batchClient->sendEvents(
            $webhook1,
            [
                [
                    'timestamp' => '2023-02-14T10:06:18Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Calls',
                        'id' => 'b6df61f0-ca62-4c62-96a6-c4c2a7ea77c6',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $clock->sleep(1);
        $this->assertSame($reqsAfter2Seconds, $reqsCount, 'time has advanced 2 seconds');

        $batchClient->sendEvents(
            $webhook2,
            [
                [
                    'timestamp' => '2023-02-14T10:06:19Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Calls',
                        'id' => 'b6df61f0-ca62-4c62-96a6-c4c2a7ea77c6',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $clock->sleep(1);
        $this->assertSame($reqsAfter3Seconds, $reqsCount, 'time has advanced 3 seconds');

        $batchClient->sendEvents(
            $webhook1,
            [
                [
                    'timestamp' => '2023-02-14T10:06:20Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Contacts',
                        'id' => 'b93a85a6-d36b-41f4-928e-2c4d710ea5f2',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $clock->sleep(1);
        $this->assertSame($reqsAfter4Seconds, $reqsCount, 'time has advanced 4 seconds');

        $batchClient->sendEvents(
            $webhook2,
            [
                [
                    'timestamp' => '2023-02-14T10:06:21Z',
                    'site_url' => 'https://example.sugarondemand.com',
                    'subscription_id' => 'be700e85-a6d3-4f6d-9b73-8793bc56942c',
                    'token' => 'abcdef',
                    'data' => [
                        'module_name' => 'Contacts',
                        'id' => 'b93a85a6-d36b-41f4-928e-2c4d710ea5f2',
                        'change_type' => 'after_save',
                        'arguments' => [],
                    ],
                ],
            ]
        );

        $clock->sleep(1);
        $this->assertSame($reqsAfter5Seconds, $reqsCount, 'time has advanced 5 seconds');

        $batchClient->flushEvents();
        $this->assertSame($reqsAfterFlush, $reqsCount, 'buffer has been flushed');
    }
}
