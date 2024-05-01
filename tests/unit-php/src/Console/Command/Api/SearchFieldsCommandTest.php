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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\SearchFieldsCommand
 */
class SearchFieldsCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->commandClass = \Sugarcrm\Sugarcrm\Console\Command\Api\SearchFieldsCommand::class;
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'searchFields';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        // TODO - cleanup different logic in a better way:
        //
        // - module_list
        // - search_only
        // - order_by_boost

        $test0 = [
            'Accounts' => [
                'name' => [
                    'name' => 'name',
                    'type' => 'name',
                    'searchable' => true,
                    'boost' => 1.9099999999999999,
                ],
                'date_entered' => [
                    'name' => 'date_entered',
                    'type' => 'datetime',
                    'searchable' => false,
                ],
                'description' => [
                    'name' => 'description',
                    'type' => 'text',
                    'searchable' => true,
                    'boost' => 0.71999999999999997,
                ],
                'phone_fax' => [
                    'name' => 'phone_fax',
                    'type' => 'phone',
                    'searchable' => true,
                    'boost' => 1.04,
                ],
            ],
            'Contracts' => [
                'name' => [
                    'name' => 'name',
                    'type' => 'name',
                    'searchable' => true,
                    'boost' => 1.5900000000000001,
                ],
                'date_entered' => [
                    'name' => 'date_entered',
                    'type' => 'datetime',
                    'searchable' => false,
                ],
                'modified_user_id' => [
                    'name' => 'modified_user_id',
                    'type' => 'id',
                    'searchable' => false,
                ],
                'description' => [
                    'name' => 'description',
                    'type' => 'text',
                    'searchable' => true,
                    'boost' => 0.63,
                ],
                'reference_code' => [
                    'name' => 'reference_code',
                    'type' => 'varchar',
                    'searchable' => true,
                    'boost' => 0.62,
                ],
            ],
        ];

        $test1 = [
            'Contacts.first_name' => 1.99,
            'Contacts.last_name' => 1.97,
            'Contacts.email' => 1.95,
            'Contacts.portal_name' => 1.9299999999999999,
            'Accounts.name' => 1.9099999999999999,
            'Accounts.email' => 1.8899999999999999,
            'Leads.first_name' => 1.8700000000000001,
            'Leads.last_name' => 1.8500000000000001,
            'Leads.email' => 1.8300000000000001,
        ];

        return [
            [
                $test0,
                [],
                'SearchFieldsCommand_0.txt',
                0,
            ],
            [
                $test1,
                ['--byBoost' => true],
                'SearchFieldsCommand_1.txt',
                0,
            ],
        ];
    }
}
