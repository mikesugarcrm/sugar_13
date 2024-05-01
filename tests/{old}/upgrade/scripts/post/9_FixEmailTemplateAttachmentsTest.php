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

require_once 'upgrade/scripts/post/9_FixEmailTemplateAttachments.php';

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SugarUpgradeFixEmailTemplateAttachments
 */
class SugarUpgradeFixEmailTemplateAttachmentsTest extends TestCase
{
    private $testTemplate;
    private $testNote;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritdoc
     *
     * Creates the necessary records for testing
     */
    protected function setUp(): void
    {
        $this->testTemplate = SugarTestEmailTemplateUtilities::createEmailTemplate();
        $this->testNote = SugarTestNoteUtilities::createNote(null, [
            'email_id' => $this->testTemplate->id,
            'email_type' => 'Emails',
        ]);
    }

    /**
     * @inheritdoc
     *
     * Cleans up any records created during the testing
     */
    protected function tearDown(): void
    {
        SugarTestEmailTemplateUtilities::removeAllCreatedEmailTemplates();
        SugarTestNoteUtilities::removeAllCreatedNotes();
    }

    /**
     * @covers ::updateNotes
     */
    public function testUpdateNotes()
    {
        // Make sure the Note has an email_type of "Emails"
        $noteBean = BeanFactory::retrieveBean('Notes', $this->testNote->id, ['use_cache' => false]);
        $this->assertEquals('Emails', $noteBean->email_type);

        // Run the updateNotes function of the upgrader
        $mockUpgrader = $this->getMockBuilder(\SugarUpgradeFixEmailTemplateAttachments::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockUpgrader->db = DBManagerFactory::getInstance();
        SugarTestReflection::callProtectedMethod($mockUpgrader, 'updateNotes');

        // Make sure the Note's email_type was changed to "EmailTemplates"
        $noteBean = BeanFactory::retrieveBean('Notes', $this->testNote->id, ['use_cache' => false]);
        $this->assertEquals('EmailTemplates', $noteBean->email_type);
    }
}
