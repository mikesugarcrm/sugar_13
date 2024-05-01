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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\SearchReindexCommand
 */
class SearchReindexCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->commandClass = \Sugarcrm\Sugarcrm\Console\Command\Api\SearchReindexCommand::class;
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'searchReindex';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        return [
            [
                ['success' => true],
                [],
                'Reindex succesfully scheduled',
                0,
            ],
            [
                ['success' => false],
                [],
                'Something went wrong, check your logs',
                1,
            ],
        ];
    }
}
