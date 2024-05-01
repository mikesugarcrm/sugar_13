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

namespace Sugarcrm\SugarcrmTestsUnit\Logger;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use SugarConfig;
use Sugarcrm\Sugarcrm\Logger\Config;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Logger\Config
 */
class ConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        SugarConfig::getInstance()->clearCache();
    }

    /**
     * @dataProvider getChannelConfigProvider
     * @covers ::getChannelConfig
     */
    public function testGetChannelConfig(array $config, $channel, $expected)
    {
        global $sugar_config;
        $sugar_config = $config;

        $sugarConfig = SugarConfig::getInstance();
        $sugarConfig->clearCache();

        $loggerConfig = new Config($sugarConfig);

        $params = $loggerConfig->getChannelConfig($channel);
        $this->assertEquals($expected, $params);
    }

    public static function getChannelConfigProvider()
    {
        return [
            'default' => [
                [],
                'default',
                [
                    'handlers' => [
                        [
                            'type' => 'file',
                            'level' => Logger::ALERT,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'default-params' => [
                [
                    'logger' => [
                        'handler' => 'gelf',
                        'level' => 'debug',
                    ],
                ],
                'default',
                [
                    'handlers' => [
                        [
                            'type' => 'gelf',
                            'level' => Logger::DEBUG,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'handler-params' => [
                [
                    'logger' => [
                        'handlers' => [
                            'file' => [
                                'dir' => '/var/log/sugar',
                            ],
                        ],
                    ],
                ],
                'default',
                [
                    'handlers' => [
                        [
                            'type' => 'file',
                            'level' => Logger::ALERT,
                            'params' => [
                                'dir' => '/var/log/sugar',
                            ],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'channel-params' => [
                [
                    'logger' => [
                        'channels' => [
                            'elastica' => [
                                'handlers' => [
                                    'chrome' => [
                                        'level' => 'debug',
                                    ],
                                    'file' => [
                                        'level' => 'debug',
                                        'name' => 'elastica',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'elastica',
                [
                    'handlers' => [
                        [
                            'type' => 'chrome',
                            'level' => Logger::DEBUG,
                            'params' => [],
                        ],
                        [
                            'type' => 'file',
                            'level' => Logger::DEBUG,
                            'params' => [
                                'name' => 'elastica',
                            ],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'handlers-as-string' => [
                [
                    'logger' => [
                        'channels' => [
                            'channel-1' => [
                                'handlers' => 'handler-1',
                            ],
                        ],
                    ],
                ],
                'channel-1',
                [
                    'handlers' => [
                        [
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'handlers-as-array-of-types' => [
                [
                    'logger' => [
                        'channels' => [
                            'channel-1' => [
                                'handlers' => ['handler-1', 'handler-2'],
                            ],
                        ],
                    ],
                ],
                'channel-1',
                [
                    'handlers' => [
                        [
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => [],
                        ],
                        [
                            'type' => 'handler-2',
                            'level' => Logger::ALERT,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'handlers-as-numeric-array' => [
                [
                    'logger' => [
                        'channels' => [
                            'channel-1' => [
                                'handlers' => [
                                    [
                                        'type' => 'handler-1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'channel-1',
                [
                    'handlers' => [
                        [
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'handlers-as-assoc-array' => [
                [
                    'logger' => [
                        'channels' => [
                            'channel-1' => [
                                'handlers' => [
                                    'handler-1' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                'channel-1',
                [
                    'handlers' => [
                        [
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'legacy-config' => [
                [
                    'log_dir' => '.',
                    'logger' => [
                        'file' => [
                            'name' => 'sugarcrm',
                            'dateFormat' => '%c',
                            'suffix' => '%Y_%m_%d',
                        ],
                    ],
                ],
                'default',
                [
                    'handlers' => [
                        [
                            'type' => 'file',
                            'level' => Logger::ALERT,
                            'params' => [
                                'name' => 'sugarcrm',
                                'dateFormat' => '%c',
                                'suffix' => '%Y_%m_%d',
                            ],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'handler-level-from-channel-handler' => [
                [
                    'logger' => [
                        'channels' => [
                            'channel-3' => [
                                'handlers' => [
                                    'handler-3' => [
                                        'level' => 'info',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'channel-3',
                [
                    'handlers' => [
                        [
                            'type' => 'handler-3',
                            'level' => Logger::INFO,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'handler-level-from-channel' => [
                [
                    'logger' => [
                        'channels' => [
                            'channel-4' => [
                                'level' => 'warning',
                                'handlers' => 'handler-4',
                            ],
                        ],
                    ],
                ],
                'channel-4',
                [
                    'handlers' => [
                        [
                            'type' => 'handler-4',
                            'level' => Logger::WARNING,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
            'logger-off' => [
                [
                    'logger' => [
                        'level' => 'off',
                    ],
                ],
                'default',
                [
                    'handlers' => [
                        [
                            'type' => 'file',
                            'level' => 0,
                            'params' => [],
                        ],
                    ],
                    'processors' => [],
                ],
            ],
        ];
    }
}
