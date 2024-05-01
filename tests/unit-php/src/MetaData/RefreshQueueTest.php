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

namespace Sugarcrm\SugarcrmTestsUnit\MetaData;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\MetaData\RefreshQueue;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\MetaData\RefreshQueue
 */
class RefreshQueueTest extends TestCase
{
    /**
     * @var RefreshQueue
     */
    private $queue;

    protected function setUp(): void
    {
        $this->queue = new RefreshQueue();
    }

    /**
     * @dataProvider queueProvider
     * @covers ::enqueue
     * @covers ::dequeue
     */
    public function testQueue(array $input, array $expected)
    {
        foreach ($input as $task) {
            [$category, $item, $params] = $task;
            $this->queue->enqueue($category, $item, $params);
        }

        $output = [];
        while ($task = $this->queue->dequeue()) {
            $output[] = $task;
        }

        $this->assertEquals($expected, $output);
    }

    public static function queueProvider()
    {
        $rebuildAccountsBase = ['rebuild',
            ['Accounts'],
            [
                'platforms' => ['base'],
            ],
        ];

        $rebuildContactsMobile = ['rebuild',
            ['Contacts'],
            [
                'platforms' => ['mobile'],
            ],
        ];

        $rebuildAccountsAll = ['rebuild',
            ['Accounts'],
            [],
        ];

        $rebuildAccountsAndContactsMobile = ['rebuild',
            ['Accounts', 'Contacts'],
            [
                'platforms' => ['mobile'],
            ],
        ];

        $rebuildContactsAndCasesAll = ['rebuild',
            ['Contacts', 'Cases'],
            [],
        ];

        return [
            'one-task-returned-as-is' => [
                [
                    $rebuildAccountsBase,
                ],
                [
                    $rebuildAccountsBase,
                ],
            ],
            'merge-params-some-and-all' => [
                [
                    $rebuildAccountsBase,
                    $rebuildAccountsAll,
                ],
                [
                    $rebuildAccountsAll,
                ],
            ],
            'merge-params-all-and-some' => [
                [
                    $rebuildAccountsAll,
                    $rebuildAccountsBase,
                ],
                [
                    $rebuildAccountsAll,
                ],
            ],
            'merge-items' => [
                [
                    $rebuildAccountsAll,
                    $rebuildContactsAndCasesAll,
                ],
                [
                    ['rebuild',
                        ['Accounts', 'Contacts', 'Cases'],
                        [],
                    ],
                ],
            ],
            'extract-item-from-sub-scope' => [
                [
                    $rebuildAccountsAndContactsMobile,
                    $rebuildContactsAndCasesAll,
                ],
                [
                    ['rebuild',
                        ['Accounts'],
                        [
                            'platforms' => ['mobile'],
                        ],
                    ],
                    ['rebuild',
                        ['Contacts', 'Cases'],
                        [],
                    ],
                ],
            ],
            'no-intersection' => [
                [
                    $rebuildAccountsBase,
                    $rebuildContactsMobile,
                ],
                [
                    $rebuildAccountsBase,
                    $rebuildContactsMobile,
                ],
            ],
        ];
    }
}
