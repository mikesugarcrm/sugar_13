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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

use PHPUnit\Framework\TestCase;
use PMSELogger;

/**
 * @coversDefaultClass \PMSELogger
 */
class PMSELoggerTest extends TestCase
{
    public function activityDataProvider()
    {
        return [
            // One tag
            [
                'message' => 'Test &0',
                'params' => [
                    'tags' => [
                        [
                            'module' => 'Foo',
                            'id' => '1',
                            'name' => 'Joe Shmoe',
                        ],
                    ],
                ],
                'expect' => [
                    'tags' => [
                        [
                            'module' => 'Foo',
                            'id' => '1',
                            'name' => 'Joe Shmoe',
                        ],
                    ],
                    'message' => 'Test @[Foo:1:Joe Shmoe]',
                ],
            ],
            // Two tags
            [
                'message' => 'Test &0 with &1',
                'params' => [
                    'tags' => [
                        [
                            'module' => 'Foo',
                            'id' => '1',
                            'name' => 'Joe Shmoe',
                        ],
                        [
                            'module' => 'Bar',
                            'id' => '2',
                            'name' => 'Sam Smith',
                        ],
                    ],
                ],
                'expect' => [
                    'tags' => [
                        [
                            'module' => 'Foo',
                            'id' => '1',
                            'name' => 'Joe Shmoe',
                        ],
                        [
                            'module' => 'Bar',
                            'id' => '2',
                            'name' => 'Sam Smith',
                        ],
                    ],
                    'message' => 'Test @[Foo:1:Joe Shmoe] with @[Bar:2:Sam Smith]',
                ],
            ],
            // Two tags with three placeholders
            [
                'message' => 'Test &0 with &1 and &2',
                'params' => [
                    'tags' => [
                        [
                            'module' => 'Foo',
                            'id' => '1',
                            'name' => 'Joe Shmoe',
                        ],
                        [
                            'module' => 'Bar',
                            'id' => '2',
                            'name' => 'Sam Smith',
                        ],
                    ],
                ],
                'expect' => [
                    'tags' => [
                        [
                            'module' => 'Foo',
                            'id' => '1',
                            'name' => 'Joe Shmoe',
                        ],
                        [
                            'module' => 'Bar',
                            'id' => '2',
                            'name' => 'Sam Smith',
                        ],
                    ],
                    'message' => 'Test @[Foo:1:Joe Shmoe] with @[Bar:2:Sam Smith] and &2',
                ],
            ],
        ];
    }

    /**
     * Tests prepareActivityData
     * @param string $message String to massage
     * @param array $params params to use to massage the string
     * @param array $expect Expectations
     * @dataProvider activityDataProvider
     * @covers ::prepareActivityData
     */
    public function testPrepareActivityData($message, $params, $expect)
    {
        $logger = PMSELogger::getInstance();
        $data = $logger->prepareActivityData($message, $params);

        $this->assertTrue(property_exists($data, 'value'));
        $this->assertTrue(property_exists($data, 'tags'));
        $this->assertSame($data->value, $expect['message']);
        $this->assertSame($data->tags, $expect['tags']);
    }

    /**
     * Tests prepareActivityData without any tags
     * @covers ::prepareActivityData
     */
    public function testPrepareActivityDataNoTags()
    {
        $message = 'This is foo';
        $logger = PMSELogger::getInstance();
        $data = $logger->prepareActivityData($message, []);

        $this->assertTrue(property_exists($data, 'value'));
        $this->assertSame($message, $data->value);
    }
}
