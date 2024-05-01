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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchRoutingCommand
 */
class ElasticsearchRoutingCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->commandClass = \Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchRoutingCommand::class;
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'elasticSearchRouting';
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
                        'strategy' => 'static',
                        'routing' => [
                            'write_index' => 'accounts',
                            'read_indices' => ['accounts'],
                        ],
                    ],
                    'emails' => [
                        'strategy' => 'archive',
                        'routing' => [
                            'write_index' => 'emails_current',
                            'read_indices' => [
                                'emails_2015_12',
                                'emails_2015_11',
                                'emails_2015_10',
                            ],
                        ],
                    ],
                ],
                [],
                'ElasticsearchRoutingCommand_0.txt',
                0,
            ],
        ];
    }
}
