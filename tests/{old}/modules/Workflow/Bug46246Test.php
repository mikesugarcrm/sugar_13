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

require_once 'include/workflow/action_utils.php';

/**
 * Relation to the document didn't created when workflow action is a document creating.
 * Test creating of the document-case relation
 */
class Bug46246Test extends TestCase
{
    private $accepted_flav = 'PRO';
    private $case_id = '52c1cd24-22e8-adb6-ac88-4f5471d6019';
    private $test_team;
    private $test_team_set_id;
    private $test_team_sets_teams_id;
    private $doc_id;

    protected function setUp(): void
    {
        if ($GLOBALS['sugar_flavor'] !== 'PRO') {
            $this->markTestSkipped('This test is for PRO flavor');
        }

        global $beanList, $beanFiles;
        require 'include/modules.php';

        if ($GLOBALS['sugar_flavor'] == $this->accepted_flav) {
            $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
            $this->test_team = SugarTestTeamUtilities::createAnonymousTeam();
            $this->test_team->add_user_to_team($GLOBALS['current_user']->id, $GLOBALS['current_user']);

            // insert test team set
            $this->test_team_set_id = create_guid();
            $GLOBALS['db']->query("INSERT INTO `team_sets` SET id='{$this->test_team_set_id}',name='test team set',team_count=1", true);

            // insert test team set relation to team
            $this->test_team_sets_teams_id = create_guid();
            $GLOBALS['db']->query("INSERT INTO `team_sets_teams` SET id='{$this->test_team_sets_teams_id}',team_set_id='{$this->test_team_set_id}'
,team_id='{$this->test_team->id}'", true);

            // create test "Case"
            $GLOBALS['db']->query("DELETE FROM `cases` WHERE id='{$this->case_id}'", true);
            $GLOBALS['db']->query("INSERT INTO `cases` SET id='{$this->case_id}',name='test case',team_id='{$this->test_team->id}',team_set_id='{$this->test_team_set_id}'", true);
        }
    }

    protected function tearDown(): void
    {
        if ($GLOBALS['sugar_flavor'] == $this->accepted_flav) {
            // delete all created records
            $GLOBALS['db']->query("DELETE FROM `cases` WHERE id='{$this->case_id}'", true);
            if ($this->doc_id) {
                $GLOBALS['db']->query("DELETE FROM `documents_cases` WHERE case_id='{$this->case_id}'", true);
                $GLOBALS['db']->query("DELETE FROM `documents` WHERE id='{$this->doc_id}'", true);
            }

            $GLOBALS['db']->query("DELETE FROM `team_sets_teams` WHERE id='{$this->test_team_sets_teams_id}'", true);
            $GLOBALS['db']->query("DELETE FROM `team_sets` WHERE id='{$this->test_team_set_id}'", true);

            SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
            SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        }
    }

    public function testRelationCreating()
    {
        $focus = BeanFactory::newBean('Cases');
        $focus->id = $this->case_id;
        $focus->save();

        $action_array = [
            'action_type' => 'new',
            'action_module' => 'documents',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic' => [
                'document_name' => 'TEST ALERT',
                'active_date' => 14440,
            ],
            'basic_ext' => [
                'active_date' => 'Triggered Date',
            ],
            'advanced' => [],
        ];
        process_action_new($focus, $action_array);


        $this->doc_id = $GLOBALS['db']->getOne("SELECT document_id FROM `documents_cases` WHERE case_id='{$this->case_id}'", true);

        // check for relation existing
        $this->assertNotEmpty($this->doc_id);
    }
}
