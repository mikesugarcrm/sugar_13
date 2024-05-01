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

class ProspectListsServiceTest extends TestCase
{
    /**
     * @var \User
     */
    //@codingStandardsIgnoreStart
    public $_user;

    //@codingStandardsIgnoreEnd

    protected function setUp(): void
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        // call a commit for transactional dbs
        $GLOBALS['db']->commit();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group prospectlistsservice
     */
    public function testAddRecordsToProspectList_AllRecordsAdded_ReturnTrue()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $contact3 = SugarTestContactUtilities::createContact();

        $recordIds = [
            $contact1->id,
            $contact2->id,
            $contact3->id,
        ];

        $prospectListService = new ProspectListsService();
        $results = $prospectListService->addRecordsToProspectList('Contacts', $prospectList->id, $recordIds);

        $this->assertEquals(3, safeCount($results), 'Three records should have been returned');
        $this->assertEquals(true, $results[$contact1->id]);
        $this->assertEquals(true, $results[$contact2->id]);
        $this->assertEquals(true, $results[$contact3->id]);
    }

    /**
     * @group prospectlistsservice
     */
    public function testAddToList_RecordNotFound_ReturnsFalse()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();
        $contactId = '111-9999';

        $recordIds = [
            $contactId,
        ];

        $prospectListService = new ProspectListsService();
        $results = $prospectListService->addRecordsToProspectList('Contacts', $prospectList->id, $recordIds);


        $this->assertEquals(1, safeCount($results), 'Three records should have been returned');
        $this->assertEquals(false, $results[$contactId]);
    }
}
