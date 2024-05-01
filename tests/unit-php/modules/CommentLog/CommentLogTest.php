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

namespace Sugarcrm\SugarcrmTestsUnit\modules\CommentLog;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommentLog
 */
class CommentLogTest extends TestCase
{
    /**
     * @covers ::setModule()
     */
    public function testSetModuleSuccess()
    {
        global $beanList;
        $commentlog = $this->createPartialMock('CommentLog', []);
        $beanList['Is Module'] = 'Is Module';

        $this->assertTrue($commentlog->setModule('Is Module'));
        $this->assertEquals($commentlog->module, 'Is Module');
    }

    /**
     * @covers ::setModule()
     */
    public function testSetMethodFailure()
    {
        $commentlog = $this->createPartialMock('CommentLog', []);

        $this->assertFalse($commentlog->setModule('Is Not Module'));
        $this->assertEmpty($commentlog->module);
    }

    /**
     * @covers ::setEntry()
     * @param string $Entry The Entry to store in the commentlog message
     * @dataProvider SetEntryProvider
     */
    public function testSetEntry(string $entry)
    {
        $commentlog = $this->createPartialMock('CommentLog', []);

        $commentlog->setEntry($entry);

        $this->assertEquals($commentlog->entry, $entry);
    }

    public function SetEntryProvider()
    {
        return [
            ['Ur the god of thunder, not the god of hammer.'],
            ['     '],
            [''],
            ['<scr' . 'ipt>console.log("Vicious matters");</scr' . 'ipt>'],
            ['<p>I have a big head</p>'],
        ];
    }

    /**
     * @covers ::createNotifications
     */
    public function testCreateNotifications()
    {
        $commentlog = $this->createPartialMock('CommentLog', [
            'load_relationship',
            'getNewBean',
            'getSugarConfigValue',
            'getModStrings',
            'getAppListStrings',
            'getBean',
            'sendPushNotification',
        ]);
        $notificationMock = $this->createPartialMock('Notifications', ['save']);
        $userMock = $this->createPartialMock('User', ['getPreference', 'canReceivePushNotifications']);
        $userMock->method('canReceivePushNotifications')->willReturn(true);
        $commentlog->method('getBean')->willReturn($userMock);
        $commentlog->method('getNewBean')->willReturn($notificationMock);
        $commentlog->method('load_relationship')->willReturn(true);
        $commentlog->method('getSugarConfigValue')->willReturn('en_us');
        $commentlog->method('getModStrings')->willReturn(
            ['LBL_YOU_HAVE_BEEN_MENTIONED' => 'You have been mentioned']
        );
        $commentlog->method('getAppListStrings')->willReturn(
            ['moduleListSingular' => ['Cases' => 'Case']]
        );
        $link2Mock = $this->createPartialMock('Link2', ['getRelatedModuleName']);
        $link2Mock->method('getRelatedModuleName')->willReturn('Cases');
        $new_rel_relname = 'cases';
        $commentlog->new_rel_relname = $new_rel_relname;
        $commentlog->$new_rel_relname = $link2Mock;
        $commentlog->entry = '@[Users:id1] and @[Users:id1] and @[Contacts:id2]';
        $notificationMock->expects($this->once())
            ->method('save');
        $commentlog->expects($this->once())
            ->method('sendPushNotification');
        $commentlog->createNotifications();
        $this->assertEquals('Case: You have been mentioned', $notificationMock->name);
        $this->assertEquals('LBL_YOU_HAVE_BEEN_MENTIONED_BY', $notificationMock->description);
        $this->assertEquals('information', $notificationMock->severity);
    }


    /**
     * @covers ::sendPushNotification
     */
    public function testSendPushNotification()
    {
        $moduleName = 'Cases';
        $recordId = 'caseId';
        $recordName = 'Case Name';
        $singularModuleName = 'Case';
        $commentlog = $this->createPartialMock('CommentLog', [
            'getBean',
            'getCurrentUser',
            'getMentionPushNotificationText',
            'createPushNotification',
        ]);
        $commentlog->method('getMentionPushNotificationText')->willReturn([
            'title' => 'Test push notification title',
            'description' => 'Test push notification description',
        ]);

        $caseMock = $this->createPartialMock('aCase', []);
        $caseMock->name = $recordName;
        $caseMock->id = $recordId;
        $commentlog->method('getBean')->willReturn($caseMock);

        $currentUserMock = $this->createPartialMock('User', []);
        $currentUserMock->full_name = 'Current User Name';
        $currentUserMock->id = 'current_user_id';
        $commentlog->method('getCurrentUser')->willReturn($currentUserMock);

        $userMock = $this->createPartialMock('User', []);
        $userMock->email1 = 'a@a.com';
        $userMock->id = 'recipient_id';

        $pushMock = $this->createPartialMock('PushNotification', [
            'send',
            'save',
        ]);
        $pushMock->method('send')->willReturn(true);
        $commentlog->method('createPushNotification')->willReturn($pushMock);

        $push = $commentlog->sendPushNotification($userMock, $moduleName, $recordId, $singularModuleName);

        $this->assertEquals('user_mentioned', $push->notification_type);
        $this->assertEquals($userMock->id, $push->assigned_user_id);
        $this->assertEquals($moduleName, $push->parent_type);
        $this->assertEquals($recordId, $push->parent_id);

        $data = json_decode($push->extra_data, true);
        $this->assertEquals($currentUserMock->id, $data['data']['mentioned_by_id']);
        $this->assertEquals($currentUserMock->full_name, $data['data']['mentioned_by_name']);
        $this->assertEquals($recordName, $data['data']['record_name']);
    }


    /**
     * @covers ::sendNotificationsEmail
     */
    public function testSendNotificationsEmail()
    {
        $moduleName = 'Cases';
        $recordId = 'caseId';
        $recordName = 'name';
        $singularModuleName = 'Case';
        $commentlog = $this->createPartialMock('CommentLog', [
            'getSugarConfigValue',
            'getBean',
            'getSystemMailer',
            'getCurrentUser',
            'getRecordUrl',
        ]);

        $templateFields = [
            'subject' => 'Subject: $initiator_full_name $singular_module_name',
            'body' => 'Body: <div><a href="$record_url">$record_name</a></div>',
            'txt_body' => 'Textbody: $record_name',
        ];
        $caseMock = $this->createPartialMock('aCase', []);
        $caseMock->name = $recordName;
        $caseMock->id = $recordId;

        $eTMock = $this->createPartialMock('EmailTemplate', []);
        $eTMock->id = 'etID';
        $eTMock->subject = $templateFields['subject'];
        $eTMock->body_html = $templateFields['body'];
        $eTMock->body = $templateFields['txt_body'];

        $commentlog->method('getBean')->will($this->onConsecutiveCalls($caseMock, $eTMock));

        $et = ['CommentLogMention' => 'etID'];
        $commentlog->method('getSugarConfigValue')->willReturn($et);
        $commentlog->method('getRecordUrl')->willReturn('http://sugarcrm.com/index.php#' .
            $moduleName . '/' . $recordId);

        $mailerMock = $this->createPartialMock('SmtpMailer', [
            'getMailTransmissionProtocol',
            'addRecipientsTo',
            'setSubject',
            'setHtmlBody',
            'setTextBody',
            'send',
        ]);
        $commentlog->method('getSystemMailer')->willReturn($mailerMock);
        $userMock = $this->createPartialMock('User', []);
        $userMock->email1 = 'a@a.com';

        $currentUserMock = $this->createPartialMock('User', []);
        $cuName = 'Current User Name';
        $currentUserMock->full_name = $cuName;
        $commentlog->method('getCurrentUser')->willReturn($currentUserMock);
        $commentlog->sendNotificationsEmail($userMock, $moduleName, $recordId, $singularModuleName);
        $expectedURL = 'http://sugarcrm.com/index.php#' . $moduleName . '/' . $recordId;
        $expectedFields = [
            'subject' => 'Subject: ' . $cuName . ' ' . strtolower($singularModuleName),
            'body' => 'Body: <div><a href="' . $expectedURL . '">' . $recordName . '</a></div>',
            'txt_body' => 'Textbody: ' . $recordName,
        ];
        $this->assertEquals($expectedFields['subject'], $eTMock->subject);
        $this->assertEquals($expectedFields['body'], $eTMock->body_html);
        $this->assertEquals($expectedFields['txt_body'], $eTMock->body);
    }
}
