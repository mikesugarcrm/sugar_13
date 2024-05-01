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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass EmailsHookHandler
 */
class EmailsHookHandlerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
    }

    /**
     * @covers ::updateAttachmentVisibility
     * @covers Email::updateAttachmentVisibility
     * @covers Note::save
     */
    public function testUpdateAttachmentVisibility()
    {
        $teams = BeanFactory::getBean('TeamSets');
        $teamSetId = $teams->addTeams(['East', 'West']);

        $email = BeanFactory::newBean('Emails');
        $email->new_with_id = true;
        $email->id = Uuid::uuid1();
        $email->name = 'SugarTest';
        $email->state = Email::STATE_ARCHIVED;
        $email->assigned_user_id = $GLOBALS['current_user']->id;
        $email->team_id = 'East';
        $email->team_set_id = $teamSetId;
        $email->team_set_selected_id = 'East';
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $note1 = SugarTestNoteUtilities::createNote();
        $note2 = SugarTestNoteUtilities::createNote();

        $email->load_relationship('attachments');
        $email->attachments->add([$note1, $note2]);

        $this->assertEquals(
            $email->assigned_user_id,
            $note1->assigned_user_id,
            'note1.assigned_user_id does not match'
        );
        $this->assertEquals(
            $email->assigned_user_id,
            $note2->assigned_user_id,
            'note2.assigned_user_id does not match'
        );
        $this->assertEquals($email->team_set_id, $note1->team_set_id, 'note1.team_set_id does not match');
        $this->assertEquals($email->team_set_id, $note2->team_set_id, 'note2.team_set_id does not match');
        $this->assertEquals($email->team_id, $note1->team_id, 'note1.team_id does not match');
        $this->assertEquals($email->team_id, $note2->team_id, 'note2.team_id does not match');
        $this->assertEquals(
            $email->team_set_selected_id,
            $note1->team_set_selected_id,
            'note1.team_set_selected_id does not match'
        );
        $this->assertEquals(
            $email->team_set_selected_id,
            $note2->team_set_selected_id,
            'note2.team_set_selected_id does not match'
        );
    }
}
