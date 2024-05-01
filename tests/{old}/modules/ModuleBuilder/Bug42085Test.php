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

class Bug42085Test extends TestCase
{
    public $meeting;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->meeting = SugarTestMeetingUtilities::createMeeting();
        //$this->listLayoutMetaDataParser = new ListLayoutMetaDataParser(MB_LISTVIEW, 'Meetings');
    }

    protected function tearDown(): void
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testHideMeetingType()
    {
        $validDef = $this->meeting->field_defs['type'];
        $this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wireless_basic_search'));
    }

    public function testHideMeetingPassword()
    {
        $validDef = $this->meeting->field_defs['password'];
        $this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelesseditview'));
        $this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelessdetailview'));
    }

    public function testHideMeetingDisplayedURL()
    {
        $validDef = $this->meeting->field_defs['displayed_url'];
        $this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelesseditview'));
        $this->assertFalse(AbstractMetaDataParser::validField($validDef, 'wirelessdetailview'));
    }
}
