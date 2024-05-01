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

class BugBr10578Test extends TestCase
{
    private $backup = [];

    protected function setUp(): void
    {
        $this->backup['BeanFactory::$bean_classes'] = serialize(BeanFactory::$bean_classes);
        $this->backup['$_REQUEST'] = serialize($_REQUEST);
    }

    protected function tearDown(): void
    {
        if (isset($this->backup['BeanFactory::$bean_classes'])) {
            BeanFactory::$bean_classes = unserialize($this->backup['BeanFactory::$bean_classes'], ['allowed_classes' => false]);
        }
        if (isset($this->backup['$_REQUEST'])) {
            $GLOBALS['_REQUEST'] = unserialize($this->backup['$_REQUEST'], ['allowed_classes' => false]);
        }
        SugarTestHelper::tearDown();
    }

    public function testShowInboundFoldersList()
    {
        BeanFactory::$bean_classes['InboundEmail'] = 'InboundEmailBr10578Mock';

        $GLOBALS['_REQUEST'] = [
            'module' => 'InboundEmail',
            'to_pdf' => '1',
            'action' => 'ShowInboundFoldersList',
            'target' => 'Popup',
            'target1' => 'Popup',
            'server_url' => 'nonexistenthost.local',
            'eapm_id' => '',
            'email_user' => 'fake_user',
            'email_password' => 'fake_password',
            'protocol' => 'imap',
            'port' => '143',
            'mailbox' => 'INBOX',
            'ssl' => 'false',
            'personal' => 'true',
            'searchField' => '',
        ];

        SugarTestHelper::setUp('mod_strings', ['InboundEmail']);
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');

        $this->setOutputCallback(fn () => '');

        include 'modules/InboundEmail/ShowInboundFoldersList.php';

        $this->assertTrue(true);
    }

    public function testInboundEmail()
    {
        SugarTestHelper::setUp('mod_strings', ['InboundEmail']);

        $ieMock = $this->getMockBuilder(InboundEmail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['preConnectMailServer'])
            ->getMock();
        $ieMock->expects($this->once())->method('preConnectMailServer')->willReturn(false);

        $result = $ieMock->connectToImapServer(true);
        $this->assertStringContainsString('check your settings', $result);
    }
}

class InboundEmailBr10578Mock extends InboundEmail
{
    public function __construct()
    {
        // skip initialization intentionally
    }

    public function retrieve($id = -1, $encode = true, $deleted = true)
    {
        return null;
    }

    public function getFoldersListForMailBox()
    {
        return [
            'status' => false,
            'statusMessage' => 'Please check your settings and try again.',
        ];
    }
}
