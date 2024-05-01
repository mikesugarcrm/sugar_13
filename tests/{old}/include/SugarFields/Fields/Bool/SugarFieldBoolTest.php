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

class SugarFieldBoolTest extends TestCase
{
    /** @var Meetings */
    private $meeting;

    /** @var SugarFieldBool */
    private $sf;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->meeting = BeanFactory::newBean('Meetings');
        $this->meeting->name = 'Awesome Test Meeting ' . create_guid();
        $this->meeting->reminder_time = 500;
        $this->meeting->email_reminder_time = 1;

        $this->sf = SugarFieldHandler::getSugarField('bool');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        unset($this->meeting);
    }

    public function testTrueBoolFieldFormatting()
    {
        $data = [];
        $service = SugarTestRestUtilities::getRestServiceMock();

        $this->meeting->reminder_checked = true;
        $this->meeting->email_reminder_checked = true;
        $this->sf->apiFormatField(
            $data,
            $this->meeting,
            [],
            'reminder_checked',
            [],
            ['reminder_checked'],
            $service
        );

        $this->assertTrue($data['reminder_checked']);

        $this->sf->apiFormatField(
            $data,
            $this->meeting,
            [],
            'email_reminder_checked',
            [],
            ['email_reminder_checked'],
            $service
        );

        $this->assertTrue($data['reminder_checked']);
    }

    public function testTrueBoolFieldUnformatting()
    {
        $result = $this->sf->unformatField(true, []);
        $this->assertTrue($result);
    }

    public function testFalseboolFieldFormatting()
    {
        // make'em false
        $this->meeting->reminder_time = -1;
        $this->meeting->email_reminder_time = -1;
        $this->meeting->reminder_checked = false;
        $this->meeting->email_reminder_checked = false;

        $data = [];
        $service = SugarTestRestUtilities::getRestServiceMock();

        $this->sf->apiFormatField(
            $data,
            $this->meeting,
            [],
            'reminder_checked',
            [],
            ['reminder_checked'],
            $service
        );

        $this->assertFalse($data['reminder_checked']);

        $this->sf->apiFormatField(
            $data,
            $this->meeting,
            [],
            'email_reminder_checked',
            [],
            ['email_reminder_checked'],
            $service
        );
        $this->assertFalse($data['email_reminder_checked']);
    }
}
