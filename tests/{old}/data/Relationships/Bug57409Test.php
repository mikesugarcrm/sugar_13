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

/**
 * Bug #57409
 * It takes 1.4 min to load Contact record edit view
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57409
 */
class Bug57409Test extends TestCase
{
    /**
     * @var Contact
     */
    protected $contact = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestOpportunityUtilities::createOpportunity();
        $opp1 = SugarTestOpportunityUtilities::createOpportunity();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('opportunities');
        $this->contact->opportunities->add($opp1->id);
    }

    protected function tearDown(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that query returns correct number of records
     *
     * @group 57409
     * @return void
     */
    public function testGetQuery()
    {
        $query = $this->contact->opportunities->relationship->getQuery($this->contact->opportunities, [
            'enforce_teams' => true,
        ]);

        $actual = 0;
        $result = $GLOBALS['db']->query($query);
        while ($GLOBALS['db']->fetchByAssoc($result, false)) {
            $actual++;
        }

        $this->assertEquals(1, $actual, 'Number of fetched opportunities is incorrect');
    }
}
