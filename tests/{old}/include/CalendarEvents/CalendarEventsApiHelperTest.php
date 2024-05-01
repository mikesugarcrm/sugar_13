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

class CalendarEventsApiHelperTest extends TestCase
{
    private $api;

    public function happyPathProvider()
    {
        return [
            [1, 1],
            ['1', '1'],
            [0, 0],
            ['0', '0'],
        ];
    }

    public function throwsMissingParameterExceptionProvider()
    {
        $now = $GLOBALS['timedate']->nowDb();
        return [
            [null, 1, 1],
            [$now, null, 1],
            [$now, 1, null],
            [$now, '', 1],
            [$now, 1, ''],
        ];
    }

    public function throwsInvalidParameterExceptionProvider()
    {
        return [
            ['a', 1],
            [1, 'a'],
            [-1, 1],
            ['-1', '1'],
            [1, -1],
            ['1', '-1'],
            [1.5, 1],
            ['1.5', '1'],
            [1, 1.5],
            ['1', '1.5'],
        ];
    }

    protected function setUp(): void
    {
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * @group bug
     * @group SI49195
     */
    public function testPopulateFromApi_ShouldNotUpdateVcal()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = '1';

        $helper = $this->getMockBuilder('CalendarEventsApiHelper')
            ->setMethods(['getInvitees'])
            ->setConstructorArgs([$this->api])
            ->getMock();
        $helper->expects($this->any())->method('getInvitees')->will($this->returnValue([]));

        $helper->populateFromApi($meeting, []);
        $this->assertFalse($meeting->update_vcal, 'Should have been set to false');
    }

    public function testPopulateFromApi_TheExistingInviteesAreAddedToTheBean()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = $GLOBALS['current_user']->id;

        $users = array_map('create_guid', array_fill(0, 5, null));
        $leads = array_map('create_guid', array_fill(0, 5, null));
        $contacts = array_map('create_guid', array_fill(0, 5, null));

        $map = [
            [$meeting, 'users', [], $users],
            [$meeting, 'leads', [], $leads],
            [$meeting, 'contacts', [], $contacts],
        ];
        $helper = $this->getMockBuilder('CalendarEventsApiHelper')
            ->setMethods(['getInvitees'])
            ->setConstructorArgs([$this->api])
            ->getMock();
        $helper->expects($this->any())->method('getInvitees')->will($this->returnValueMap($map));

        $helper->populateFromApi($meeting, []);
        $this->assertCount(
            count($users),
            $meeting->users_arr,
            'Should have the number of generated users'
        );
        $this->assertCount(count($leads), $meeting->leads_arr, 'Should have the number of generated leads');
        $this->assertCount(count($contacts), $meeting->contacts_arr, 'Should have the number of generated contacts');
    }

    /**
     * @dataProvider happyPathProvider
     */
    public function testPopulateFromApi_ReturnsTrue($hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new CalendarEventsApiHelper($this->api);
        $actual = $helper->populateFromApi($meeting, []);
        $this->assertTrue($actual, 'The happy path should have returned true');
    }

    /**
     * @dataProvider throwsMissingParameterExceptionProvider
     */
    public function testPopulateFromApi_ThrowsMissingParameterException($starts, $hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $starts;
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new CalendarEventsApiHelper($this->api);

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $helper->populateFromApi($meeting, []);
    }

    /**
     * @dataProvider throwsInvalidParameterExceptionProvider
     */
    public function testPopulateFromApi_ThrowsInvalidParameterException($hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new CalendarEventsApiHelper($this->api);

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $helper->populateFromApi($meeting, []);
    }

    public function testFormatForApi_MeetingIsRelatedToAContact_TheNameOfTheContactIsAddedToTheResponse()
    {
        $meeting = $this->getMockBuilder('Meeting')->setMethods(['ACLAccess'])->getMock();
        $meeting->expects($this->any())->method('ACLAccess')->will($this->returnValue(true));
        BeanFactory::setBeanClass('Meetings', get_class($meeting));
        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $contact = SugarTestContactUtilities::createContact();
        $meeting->contact_id = $contact->id;

        $helper = new CalendarEventsApiHelper($this->api);
        $data = $helper->formatForApi($meeting);
        $this->assertEquals($data['contact_name'], $contact->full_name, "The contact's name does not match");

        BeanFactory::unregisterBean($meeting);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testGetInvitees_ReturnsCorrectDataForLink()
    {
        $meeting = $this->createPartialMock('Meeting', ['load_relationship']);
        $meeting->expects($this->any())->method('load_relationship')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Meetings', get_class($meeting));

        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $contactsId1 = create_guid();
        $contactsId2 = create_guid();
        $leadsId1 = create_guid();
        $usersId1 = create_guid();

        $submittedData = [
            'contacts' => [
                'add' => [
                    $contactsId1,
                    [
                        'id' => $contactsId2,
                    ],
                ],
            ],
            'leads' => [
                'add' => [
                    $leadsId1,
                ],
                'delete' => [],
            ],
            'users' => [
                'delete' => [
                    $usersId1,
                ],
            ],
        ];
        $helper = new CalendarEventsApiHelperMock($this->api);

        $invitees = $helper->getInvitees($meeting, 'contacts', $submittedData);
        $this->assertCount(2, $invitees, 'Should include two contacts in the list');
        $this->assertContains($contactsId1, $invitees);
        $this->assertContains($contactsId2, $invitees);

        $invitees = $helper->getInvitees($meeting, 'leads', $submittedData);
        $this->assertCount(1, $invitees, 'Should include both the assigned user and current user');
        $this->assertContains($leadsId1, $invitees);

        $invitees = $helper->getInvitees($meeting, 'users', $submittedData);
        $this->assertCount(0, $invitees, 'Should include both the assigned user and current user');

        BeanFactory::unregisterBean($meeting);
        BeanFactory::setBeanClass('Meetings');
    }
}

/*
 * Mock class to test protected methods
 */

class CalendarEventsApiHelperMock extends CalendarEventsApiHelper
{
    public function getInvitees(SugarBean $bean, $link, $submittedData)
    {
        return parent::getInvitees($bean, $link, $submittedData);
    }
}
