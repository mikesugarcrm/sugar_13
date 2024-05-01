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
 * @coversDefaultClass EmailsApiHelper
 */
class EmailsApiHelperTest extends TestCase
{
    private $helper;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $api = SugarTestRestUtilities::getRestServiceMock();
        $this->helper = new EmailsApiHelper($api);
    }

    protected function tearDown(): void
    {
        SugarTestNoteUtilities::removeAllCreatedNotes();
    }

    /**
     * @covers ::formatForApi
     */
    public function testFormatForApi()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->new_with_id = false;
        $bean->id = Uuid::uuid1();
        $bean->name = 'Renewal notice';
        $bean->state = Email::STATE_DRAFT;
        // There is no outbound email account with that ID.
        $bean->outbound_email_id = Uuid::uuid1();

        $fieldList = [
            'id',
            'name',
            'state',
            'outbound_email_id',
        ];
        $data = $this->helper->formatForApi($bean, $fieldList);

        // Testing for these attributes is unnecessary.
        unset($data['_acl']);

        $expected = [
            'id' => $bean->id,
            'name' => $bean->name,
            'state' => $bean->state,
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * @covers ::formatForApi
     */
    public function testFormatForApi_InlineImagesAreConverted()
    {
        $noteId1 = Uuid::uuid1();
        $noteId2 = Uuid::uuid1();
        $uploadId = Uuid::uuid1();

        $cid1 = "cid:{$noteId1}.gif";
        $cid2 = "cid:{$noteId2}.png";

        $version = (new ApiSettings())->formatVersionForUrl();
        $expected1 = "/rest/{$version}/Notes/{$noteId1}/file/filename?force_download=0&platform=base";
        $expected2 = "/rest/{$version}/Notes/{$noteId2}/file/filename?force_download=0&platform=base";

        $bean = BeanFactory::newBean('Emails');
        $bean->new_with_id = false;
        $bean->id = Uuid::uuid1();
        $bean->name = 'Renewal notice';
        $bean->state = Email::STATE_ARCHIVED;
        $bean->description_html = <<<EOHTML
<html>
<p><img border="0" width="1" height="20" class="image" src="{$cid1}"/></p>
<p><img border="0" width="1" height="30" class="image" src="{$cid2}"/></p>
</html>
EOHTML;

        $note1 = SugarTestNoteUtilities::createNote(
            $noteId1,
            [
                'email_id' => $bean->id,
                'file_mime_type' => 'image/gif',
            ]
        );
        $note2 = SugarTestNoteUtilities::createNote(
            $noteId2,
            [
                'email_id' => $bean->id,
                'file_mime_type' => 'image/png',
                'upload_id' => $uploadId,
            ]
        );

        $fieldList = [
            'id',
            'name',
            'description_html',
        ];

        $data = $this->helper->formatForApi($bean, $fieldList);

        $this->assertStringContainsString($expected1, $data['description_html'], 'Image 1 not converted');
        $this->assertStringContainsString($expected2, $data['description_html'], 'Image 2 not converted');
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_OutboundEmailIdNotFound()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => Uuid::uuid1(),
        ];

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->helper->populateFromApi($bean, $submittedData);
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_AssignedUserSuppliedIsValidIfCurrentUserId()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Expected Success when Supplying Current User ID On Save As Draft');
        $this->assertSame(
            $bean->assigned_user_id,
            $GLOBALS['current_user']->id,
            'Expected Bean Assigned User Id to be Current User Id when Saving As Draft'
        );
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_AssignedUserIsDefaultedToTheCurrentUserForDraftEmail()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'state' => Email::STATE_DRAFT,
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Expected Success when Supplying Current User ID On Save As Draft');
        $this->assertSame(
            $bean->assigned_user_id,
            $GLOBALS['current_user']->id,
            'Expected Bean Assigned User Id to be Current User Id when Saving As Draft'
        );
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_AssignedUserIsDefaultedToTheCurrentUserForArchivedEmail()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'state' => Email::STATE_ARCHIVED,
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Expected Success when not specifying Assigned User On Save As Archived');
        $this->assertSame(
            $bean->assigned_user_id,
            $GLOBALS['current_user']->id,
            'Expected Bean Assigned User Id to be Current User Id when Saving Archived without an assigned_user_id'
        );
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_UnassignedUserIsValidForArchivedEmail()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => '',
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Expected Success when blanking Assigned User On Save As Archived');
        $this->assertEmpty(
            $bean->assigned_user_id,
            'Expected Bean Assigned User Id to be Unassigned'
        );
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_AssignedUserSuppliedDoesNotMatchCurrentUserId()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();

        $submittedData = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => Uuid::uuid1(),
        ];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->helper->populateFromApi($bean, $submittedData);
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_CannotSpecifySenderForDraft()
    {
        $bean = BeanFactory::newBean('Emails');

        $submittedData = [
            'state' => Email::STATE_DRAFT,
            'from' => [
                'create' => [
                    'parent_type' => 'Users',
                    'parent_id' => Uuid::uuid1(),
                    'email_address_id' => Uuid::uuid1(),
                ],
            ],
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->helper->populateFromApi($bean, $submittedData);
    }

    public function isUpdateProvider()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider isUpdateProvider
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_StateChangeIsInvalid($isUpdate)
    {
        $bean = $this->createPartialMock('Email', ['isUpdate', 'isStateTransitionAllowed']);
        $bean->method('isUpdate')->willReturn($isUpdate);
        $bean->method('isStateTransitionAllowed')->willReturn(false);

        $submittedData = [
            'state' => 'Foo',
        ];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->helper->populateFromApi($bean, $submittedData);
    }

    /**
     * @covers ::populateFromApi
     */
    public function testPopulateFromApi_PopulatedAsDraftIfStateIsReady()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'state' => Email::STATE_READY,
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Populating was not successful');
        $this->assertSame(Email::STATE_DRAFT, $bean->state, 'The email should be a draft');
    }

    public function testPopulateFromApi_MaintainBackwardCompatibilityForCreatingDrafts()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'type' => 'draft',
            'status' => 'draft',
            'to_addrs_names' => '"Wenona Seely" <hr.kid.info@example.co.uk>, "Annie Cotter" <kid.vegan@example.de>',
            'name' => 'Thanks for your time today',
            'description_html' => 'We should chat again tomorrow!',
            'description' => 'We should chat again tomorrow!',
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Populating was not successful');
        $this->assertSame(Email::STATE_DRAFT, $bean->state, 'The email should be a draft');
        $this->assertSame('draft', $bean->type, 'type should be draft');
        $this->assertSame('draft', $bean->status, 'status should be draft');
        $this->assertSame($submittedData['to_addrs_names'], $bean->to_addrs, 'The TO recipients were not mapped');
    }

    public function testPopulateFromApi_MaintainBackwardCompatibilityForUpdatingDrafts()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->state = Email::STATE_DRAFT;

        $submittedData = [
            'to_addrs_names' => '"Wenona Seely" <hr.kid.info@example.co.uk>, "Annie Cotter" <kid.vegan@example.de>',
            'cc_addrs_names' => 'eddie.hammer@example.co.uk',
            'bcc_addrs_names' => '"Tom Vernon" <tvernon@example.com>',
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Populating was not successful');
        $this->assertSame($submittedData['to_addrs_names'], $bean->to_addrs, 'The TO recipients were not mapped');
        $this->assertSame($submittedData['cc_addrs_names'], $bean->cc_addrs, 'The CC recipients were not mapped');
        $this->assertSame($submittedData['bcc_addrs_names'], $bean->bcc_addrs, 'The BCC recipients were not mapped');
    }

    public function testPopulateFromApi_MaintainBackwardCompatibilityForCreatingArchivedEmails()
    {
        $bean = BeanFactory::newBean('Emails');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;

        $submittedData = [
            'type' => 'archived',
            'status' => 'read',
            'from_addr_name' => '"Will Westin" <will@example.com>',
            'to_addrs_names' => '"Wenona Seely" <hr.kid.info@example.co.uk>, "Annie Cotter" <kid.vegan@example.de>',
            'cc_addrs_names' => 'eddie.hammer@example.co.uk',
            'bcc_addrs_names' => '"Tom Vernon" <tvernon@example.com>',
            'name' => 'I want your business',
            'description_html' => 'We can deliver on our promises.',
            'description' => 'We can deliver on our promises.',
        ];

        $result = $this->helper->populateFromApi($bean, $submittedData);
        $this->assertTrue($result, 'Populating was not successful');
        $this->assertSame(Email::STATE_ARCHIVED, $bean->state, 'The email should be archived');
        $this->assertSame('archived', $bean->type, 'type should be archived');
        $this->assertSame('read', $bean->status, 'status should be read');
        $this->assertSame($submittedData['from_addr_name'], $bean->from_addr, 'The sender was not mapped');
        $this->assertSame($submittedData['to_addrs_names'], $bean->to_addrs, 'The TO recipients were not mapped');
        $this->assertSame($submittedData['cc_addrs_names'], $bean->cc_addrs, 'The CC recipients were not mapped');
        $this->assertSame($submittedData['bcc_addrs_names'], $bean->bcc_addrs, 'The BCC recipients were not mapped');
    }
}
