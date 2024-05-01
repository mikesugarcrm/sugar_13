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
 * @coversDefaultClass EmailAttachmentRelationship
 */
class EmailAttachmentRelationshipTest extends TestCase
{
    private $relationship;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestNoteUtilities::removeAllCreatedNotes();
    }

    protected function setUp(): void
    {
        $this->relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_attachments');
    }

    protected function tearDown(): void
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);
    }

    /**
     * Email attachments can be linked when an email is a draft.
     *
     * @covers ::add
     * @covers ::updateFields
     * @covers SugarRelationship::addToResaveList
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_CanLinkWhenEmailIsADraft()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $note = SugarTestNoteUtilities::createNote();

        $result = $this->relationship->add($email, $note);

        $this->assertTrue($result, 'Linking was unsuccessful');
        $this->assertSame('Emails', $note->email_type, 'Should be Emails');
        $this->assertSame($email->id, $note->email_id, 'Should reference the email');
    }

    /**
     * Email attachments cannot be linked when an email is archived.
     *
     * @covers ::add
     */
    public function testAdd_CannotLinkWhenEmailIsArchived()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $note = SugarTestNoteUtilities::createNote();

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->add($email, $note);
    }

    /**
     * Email attachments can be linked when an email is archived and it is new.
     *
     * @covers ::add
     * @covers ::updateFields
     * @covers SugarRelationship::addToResaveList
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_CanLinkWhenEmailIsArchivedButHasNotBeenSavedYet()
    {
        $email = BeanFactory::newBean('Emails');
        $email->new_with_id = true;
        $email->id = Uuid::uuid1();
        $email->name = 'SugarEmail';
        $email->state = Email::STATE_ARCHIVED;
        SugarTestEmailUtilities::setCreatedEmail($email->id);
        $note = SugarTestNoteUtilities::createNote();

        $result = $this->relationship->add($email, $note);

        $this->assertTrue($result, 'Linking was unsuccessful');
        $this->assertSame('Emails', $note->email_type, 'Should be Emails');
        $this->assertSame($email->id, $note->email_id, 'Should reference the email');
    }

    /**
     * Email attachments can be unlinked when an email is a draft.
     *
     * @covers ::remove
     * @covers Note::mark_deleted
     */
    public function testRemove_CanUnlinkWhenEmailIsADraft()
    {
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $note = SugarTestNoteUtilities::createNote('', ['email_type' => 'Emails', 'email_id' => $email->id]);

        $result = $this->relationship->remove($email, $note);

        $this->assertTrue($result, 'Unlinking was unsuccessful');
        $this->assertEquals(1, $note->deleted, 'The attachment should have been deleted');
        $this->assertEmpty($note->email_id, 'Should not reference the email');
    }

    /**
     * Email attachments cannot be unlinked when an email is archived.
     *
     * @covers ::remove
     */
    public function testRemove_CannotUnlinkWhenEmailIsArchived()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $note = SugarTestNoteUtilities::createNote('', ['email_type' => 'Emails', 'email_id' => $email->id]);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->relationship->remove($email, $note);
    }

    /**
     * Email attachments can be unlinked when an email is archived and the attachment is being deleted.
     *
     * @covers ::remove
     * @covers Note::mark_deleted
     */
    public function testRemove_CanUnlinkWhenEmailIsArchivedAndAttachmentIsBeingDeleted()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $note = SugarTestNoteUtilities::createNote('', ['email_type' => 'Emails', 'email_id' => $email->id]);

        $note->mark_deleted($note->id);

        $this->assertEquals(1, $note->deleted, 'The attachment should have been deleted');
        $this->assertEmpty($note->email_id, 'Should not reference the email');
    }

    /**
     * Email attachments can be unlinked when an email is archived and the email is being deleted.
     *
     * @covers ::remove
     * @covers Email::mark_deleted
     * @covers Note::mark_deleted
     */
    public function testRemove_CanUnlinkWhenEmailIsArchivedAndEmailIsBeingDeleted()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $note = SugarTestNoteUtilities::createNote('', ['email_type' => 'Emails', 'email_id' => $email->id]);

        $email->mark_deleted($email->id);

        $this->assertEquals(1, $note->deleted, 'The attachment should have been deleted');
        $this->assertEmpty($note->email_id, 'Should not reference the email');
    }
}
