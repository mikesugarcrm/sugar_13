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

require_once 'include/export_utils.php';

class Bug43805Test extends TestCase
{
    /**
     * Contains created prospect lists' ids
     * @var array
     */
    private static $createdProspectListsIds = [];

    /**
     * Instance of ProspectList
     * @var ProspectList
     */
    private $prospectList;

    /**
     * prospects array
     * @var array
     */
    private $prospects = [];

    /**
     * Create prospect instance (with account)
     */
    public static function createProspect()
    {
        $prospect = SugarTestProspectUtilities::createProspect();

        $prospect->save();
        return $prospect;
    }

    /**
     * Create ProspectList instance
     * @param prospect instance to attach to prospect list
     */
    public static function createProspectList($prospect = null)
    {
        $prospectList = new ProspectList();
        $prospectList->name = 'TargetList_code';
        $prospectList->save();
        self::$createdProspectListsIds[] = $prospectList->id;

        if ($prospect instanceof Prospect) {
            self::attachProspectToProspectList($prospectList, $prospect);
        }

        return $prospectList;
    }

    /**
     * Attach Prospect to prospect list
     * @param ProspectList $prospectList prospect list instance
     * @param prospect $prospect prospect instance
     */
    public static function attachProspectToProspectList($prospectList, $prospect)
    {
        $prospectList->load_relationship('prospects');
        $prospectList->prospects->add($prospect->id, []);
    }

    /**
     * Set up - create prospect list with 1 prospect
     */
    protected function setUp(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        ;

        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $this->prospects[] = self::createProspect();
        $this->prospectList = self::createProspectList($this->prospects[0]);
        self::attachProspectToProspectList($this->prospectList, $this->prospects[0]);
    }

    protected function tearDown(): void
    {
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->clearProspects();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * Test if Title exists within report
     */
    public function testTitleExistsExportList()
    {
        $content = export('ProspectLists', [$this->prospectList->id], true);
        $this->assertStringContainsString($this->prospects[0]->title, $content);
    }

    private function clearProspects()
    {
        $ids = implode("', '", self::$createdProspectListsIds);
        $GLOBALS['db']->query('DELETE FROM prospect_list_campaigns WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists_prospects WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . $ids . '\')');
    }
}
