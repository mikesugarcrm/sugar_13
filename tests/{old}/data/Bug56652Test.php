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

class Bug56652Test extends TestCase
{
    /** @var Contact */
    protected $contact;

    /**
     * Account names are randomly sorted in order to make sure that the data is
     * properly sorted by the application
     *
     * @var array
     */
    protected $account_names = [
        'E', 'G', 'A', 'D', 'B', 'H', 'F', 'C',
    ];

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('opportunities');
        foreach ($this->account_names as $account_name) {
            $account = SugarTestAccountUtilities::createAccount();
            $account->name = $account_name;
            $account->save(false);

            $opportunity = SugarTestOpportunityUtilities::createOpportunity(null, $account);
            $this->contact->opportunities->add($opportunity);
        }
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    /**
     * @param string $order
     * @param string $function
     * @dataProvider getOrders
     */
    public function testSubPanelDataIsSorted($order, $function)
    {
        // create a minimum required subpanel definition
        $subPanel = new aSubPanel(null, [
            'module' => 'Opportunities',
            'subpanel_name' => null,
            'get_subpanel_data' => 'opportunities',
        ], $this->contact);

        // fetch subpanel data
        $response = SugarBean::get_union_related_list(
            $this->contact,
            'account_name',
            $order,
            '',
            0,
            -1,
            -1,
            0,
            $subPanel
        );

        $this->assertArrayHasKey('list', $response);

        $account_names = [];

        /** @var Opportunity $opportunity */
        foreach ($response['list'] as $opportunity) {
            $account_names[] = $opportunity->account_name;
        }

        $sorted = $account_names;
        $function($sorted);

        // ensure that opportunities are sorted by account name in the needed order
        $this->assertSame($sorted, $account_names);
    }

    /**
     * @return array
     */
    public static function getOrders()
    {
        return [
            ['asc', 'sort'],
            ['desc', 'rsort'],
        ];
    }
}
