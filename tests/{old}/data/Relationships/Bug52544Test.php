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

class Bug52544Test extends TestCase
{
    /**
     * @var User
     */
    protected $user1;

    protected function setUp(): void
    {
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user2->reports_to_id = $this->user1->id;
        $user2->save();
        $user3 = SugarTestUserUtilities::createAnonymousUser();
        $user3->reports_to_id = $this->user1->id;
        $user3->save();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testGetSubpanelQueryReturnsArray()
    {
        // Load up the relationship
        if (!$this->user1->load_relationship('reportees')) {
            // The relationship did not load, I'm guessing it doesn't exist
            $this->fail('Could not find a relationship named: reportees');
        }
        $linkQueryParts = $this->user1->reportees->getSubpanelQuery(['return_as_array' => true]);

        $this->assertTrue(is_array($linkQueryParts));
    }
}
