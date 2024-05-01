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

class NotesApiHelperTest extends TestCase
{
    protected $contact;
    protected $note;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $this->setupContact();
        $this->setupNote();
    }

    protected function tearDown(): void
    {
        // Good housekeeping is always appreciated
        unset($this->contact, $this->note);
        SugarTestHelper::tearDown();
    }

    public function testAddPortalUserDataToBean()
    {
        $helper = new NotesApiHelper(new NotesServiceMock());
        $helper->addPortalUserDataToBean($this->contact, $this->note);

        $this->assertSame('external', $this->note->entry_source);
        $this->assertSame('contact-assigned-user-01', $this->note->assigned_user_id);
        $this->assertSame('contact-team-01', $this->note->team_id);
        $this->assertSame('contact-team-set-01', $this->note->team_set_id);
        $this->assertSame('contact-acl-team-set-01', $this->note->acl_team_set_id);

        $this->assertSame('contact-account-01', $this->note->account_id);
        $this->assertSame('contact-01', $this->note->contact_id);
    }

    protected function setupContact()
    {
        // We need both a contact and a note for this
        $this->contact = BeanFactory::newBean('Contacts');
        $this->contact->id = 'contact-01';
        $this->contact->assigned_user_id = 'contact-assigned-user-01';
        $this->contact->account_id = 'contact-account-01';
        $this->contact->fetched_row['team_id'] = 'contact-team-01';
        $this->contact->fetched_row['team_set_id'] = 'contact-team-set-01';
        $this->contact->fetched_row['acl_team_set_id'] = 'contact-acl-team-set-01';
    }

    protected function setupNote()
    {
        $this->note = BeanFactory::newBean('Notes');
        $this->note->name = 'test-note-01';
    }
}

/**
 * Helper to allow actual testing
 */
class NotesServiceMock extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
