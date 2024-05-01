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

/*
* This test check if prosect adds correctly to prospects_list
* @ticket 53288
*/


class Bug53288Test extends TestCase
{
    private $oProspectList;
    private $oProspect;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', [true, 1]);
        $this->oProspect = SugarTestProspectUtilities::createProspect();
        $this->createProspectList();
    }

    protected function tearDown(): void
    {
        SugarTestProspectListsUtilities::removeProspectsListToProspectRelation($this->oProspectList->id, $this->oProspect->id);
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestProspectListsUtilities::removeProspectLists($this->oProspectList->id);
        $_REQUEST = [];
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testAddProspectsToProspectList()
    {
        $_REQUEST['prospect_list_id'] = $this->oProspectList->id;
        $_REQUEST['prospect_id'] = $this->oProspect->id;
        $_REQUEST['prospect_ids'] = [$this->oProspect->id];
        $_REQUEST['return_type'] = 'addtoprospectlist';
        require 'include/generic/Save2.php';
        $res = $GLOBALS['db']->query("SELECT * FROM prospect_lists_prospects WHERE prospect_list_id='{$this->oProspectList->id}' AND related_id='{$this->oProspect->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($res);
        $this->assertIsArray($row);
    }

    protected function createProspectList()
    {
        $this->oProspectList = new ProspectList();
        $this->oProspectList->name = 'Bug53288Test_ProspectListName';
        $this->oProspectList->save();
    }
}
