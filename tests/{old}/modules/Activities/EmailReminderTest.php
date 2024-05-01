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
 * @coversDefaultClass EmailReminder
 */
class EmailReminderTest extends TestCase
{
    protected function tearDown(): void
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        parent::tearDown();
    }

    /**
     * @covers ::setReminderBody
     */
    public function testSetReminderBody_ReturnsTemplateFormattedCorrectly()
    {
        $timedate = null;
        global $current_user;

        $sugarConfig = SugarConfig::getInstance();
        $timeDate = TimeDate::getInstance();

        $template = $sugarConfig['default_language'];
        $xtpl = new XTemplate(get_notify_template_file($template));

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting = BeanFactory::retrieveBean('Meetings', $meeting->id);

        $emailReminder = new EmailReminder();

        $xtpl = SugarTestReflection::callProtectedMethod(
            $emailReminder,
            'setReminderBody',
            [$xtpl, $meeting, $current_user]
        );

        $xtpl->parse('MeetingReminder');
        $xtpl->parse('MeetingReminder_Subject');

        $meetingSubject = "/{$meeting->name}/";
        $startDate = $timedate->fromDB($meeting->date_start);
        $meetingStartDate = "/{$timedate->asUser($startDate, $current_user)}/";

        $this->assertMatchesRegularExpression($meetingSubject, $xtpl->text('MeetingReminder_Subject'), 'The subject is not set properly');
        $this->assertMatchesRegularExpression($meetingSubject, $xtpl->text('MeetingReminder'), 'The body does not contain the start date');
    }
}
