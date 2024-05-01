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

/**
 * @group bug43282
 */
class SetEntryTest extends SOAPTestCase
{
    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = new Task();
        $this->task->name = 'Unit Test';
        $this->task->save();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM tasks WHERE id = '{$this->task->id}'");
        parent::tearDown();
    }

    /**
     * Ensure that when updating the team_id value for a bean that the team_set_id is not
     * populated into the team_id field if the team_id value is already set.
     *
     * @return void
     */
    public function testUpdateRecordsTeamID()
    {
        $privateTeamID = $GLOBALS['current_user']->getPrivateTeamID();

        $this->login();
        $result = $this->soapClient->set_entry(
            $this->sessionId,
            'Tasks',
            [
                ['name' => 'id', 'value' => $this->task->id],
                ['name' => 'team_id', 'value' => $privateTeamID],
            ]
        );

        $modifiedTask = new Task();
        $modifiedTask->retrieve($this->task->id);
        $this->assertEquals($privateTeamID, $modifiedTask->team_id);
    }
}
