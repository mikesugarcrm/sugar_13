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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Api;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchIndicesCommand
 */
class ElasticsearchIndicesCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->commandClass = \Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchIndicesCommand::class;
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'elasticSearchIndices';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        return [
            [
                [
                    'accounts' => [
                        'indices' => [
                            'accounts' => [
                                'total' => [
                                    'docs' => [
                                        'count' => 5684,
                                        'max_doc' => 5687,
                                        'deleted_docs' => 3,
                                    ],
                                    'store' => [
                                        'primary_size_in_bytes' => 256854,
                                        'size_in_bytes' => 256854,
                                    ],
                                ],
                            ],
                        ],
                        '_shards' => [
                            'total' => 3,
                            'successful' => 3,
                            'failed' => 0,
                        ],
                    ],
                    'contacts' => [
                        'indices' => [
                            'contacts' => [
                                'total' => [
                                    'docs' => [
                                        'count' => 542568,
                                        'max_doc' => 542568,
                                        'deleted_docs' => 0,
                                    ],
                                    'store' => [
                                        'primary_size_in_bytes' => 254686854,
                                        'size_in_bytes' => 254686854,
                                    ],
                                ],
                            ],
                        ],
                        '_shards' => [
                            'total' => 5,
                            'successful' => 5,
                            'failed' => 0,
                        ],
                    ],
                ],
                [],
                'ElasticsearchIndicesCommand_0.txt',
                0,
            ],
        ];
    }
}
