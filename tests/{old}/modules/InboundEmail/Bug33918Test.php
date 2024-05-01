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

require_once 'modules/Campaigns/ProcessBouncedEmails.php';

/**
 * @ticket 33918
 */
class Bug33918Test extends TestCase
{
    private $user;
    private $team;
    private $ie;

    protected function setUp(): void
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->user->default_team = $this->team->id;
        $this->team->add_user_to_team($this->user->id);
        $this->user->save();
        $this->ie = new InboundEmail();

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$this->user->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
    }


    public function testGetExistingCampaignLogEntry()
    {
        $targetTrackerKey = create_guid();
        $campaignLogID = create_guid();

        $tst = new CampaignLog();
        $tst->activity_type = 'targeted';
        $tst->target_tracker_key = $targetTrackerKey;
        $tst->id = $campaignLogID;
        $tst->new_with_id = true;
        $tst->save(false);

        $row = getExistingCampaignLogEntry($targetTrackerKey);

        $this->assertEquals($tst->activity_type, $row['activity_type'], 'Unable to get existing bounced campaign log entry');
        $this->assertEquals($tst->id, $row['id'], 'Unable to get existing bounced campaign log entry');
        $this->assertEquals($tst->target_tracker_key, $row['target_tracker_key'], 'Unable to get existing bounced campaign log entry');

        $emptyRow = getExistingCampaignLogEntry(uniqid());
        $this->assertTrue(empty($emptyRow), 'Unable to get existing bounced campaign log entry');

        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id='$campaignLogID'");
    }

    public function testCreateBouncedCampaignLogEntry()
    {
        $row = [
            'campaign_id' => create_guid(),
            'target_tracker_key' => create_guid(),
            'target_id' => create_guid(),
            'target_type' => 'Lead',
            'list_id' => create_guid(),
            'marketing_id' => create_guid(),

        ];
        $email = new stdClass();
        $email->date_created = gmdate('Y-m-d H:i:s');
        $email->id = uniqid();
        $email_description = ' Unit test with permanent[undeliverable] error ';


        $bounce_id = createBouncedCampaignLogEntry($row, $email, $email_description);
        $bounce = new CampaignLog();
        $bounce->retrieve($bounce_id);

        $this->assertEquals($row['campaign_id'], $bounce->campaign_id, 'Unable to create bounced campaign log entry');
        $this->assertEquals($row['target_id'], $bounce->target_id, 'Unable to create bounced campaign log entry');
        $this->assertEquals($row['marketing_id'], $bounce->marketing_id, 'Unable to create bounced campaign log entry');
        $this->assertEquals('send error', $bounce->activity_type, 'Unable to create bounced campaign log entry');
        $this->assertEquals($email->id, $bounce->related_id, 'Unable to create bounced campaign log entry');

        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id='$bounce_id'");
    }

    public function testErrorReportRetrieval()
    {
        $noteID = uniqid();
        $emailId = uniqid();

        $note = new Note();
        $note->description = 'Unit Test';
        $note->file_mime_type = 'message/rfc822';
        $note->subject = 'Unit Test';
        $note->new_with_id = true;
        $note->id = $noteID;
        $note->email_id = $emailId;
        $note->email_type = 'Emails';
        $note->save();

        $email = new stdClass();
        $email->id = $emailId;

        $emailEmpty = new stdClass();
        $emailEmpty->id = '1234';

        $this->assertEquals($note->description, retrieveErrorReportAttachment($email), 'Unable to retrieve error report for bounced email');
        $this->assertEquals('', retrieveErrorReportAttachment($emailEmpty), 'Unable to retrieve error report for bounced email');
        $GLOBALS['db']->query("DELETE FROM notes WHERE id='{$note->id}'");
    }

    /**
     * @dataProvider breadCrumbOffsetsData
     */
    public function testAddBreadCrumbOffset($base, $offset, $expected)
    {
        $rs = $this->ie->addBreadCrumbOffset($base, $offset);
        $this->assertEquals($expected, $rs, 'Unable to add bread crumb offset');
    }

    public static function breadCrumbOffsetsData()
    {
        return [
            ['base' => '1.0', 'offset' => '0.1', 'expected' => '1.1'],
            ['base' => '2.0', 'offset' => '1.0', 'expected' => '3.0'],
            ['base' => '1.0.1', 'offset' => '0.1', 'expected' => '1.1.1'],
            ['base' => '4', 'offset' => '0.1', 'expected' => '4.1'],
            ['base' => '0.0', 'offset' => '0.1', 'expected' => '0.1'],
            ['base' => '0', 'offset' => '0', 'expected' => '0'],

        ];
    }


    /**
     * @dataProvider gmailEmailData
     */
    public function testProcessBouncedGmailEmailWithIdentifierInHeader($trackerKey, $message)
    {
        $noteID = uniqid();
        $emailId = uniqid();
        $note = new Note();
        $note->description = $message;
        $note->file_mime_type = 'message/rfc822';
        $note->subject = 'Unit Test';
        $note->new_with_id = true;
        $note->id = $noteID;
        $note->email_id = $emailId;
        $note->email_type = 'Emails';
        $note->save();

        $email = new stdClass();
        $email->id = $emailId;
        $email->description = $message;
        $email->raw_source = $message;
        $email->date_created = gmdate('Y-m-d H:i:s');
        $logID = $this->createCampaignLogForTrackerKey($trackerKey);
        $email_header = new stdClass();
        $email->from_name = 'Mail Delivery Subsystem <mailer-daemon@googlemail.com>';
        $this->assertTrue(campaign_process_bounced_emails($email, $email_header), 'Unable to process bounced email');

        $GLOBALS['db']->query("DELETE FROM notes WHERE id='{$note->id}'");
        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id='{$logID}' OR target_tracker_key='{$trackerKey}'");
    }


    public function testProcessBouncedEmail()
    {
        $trackerKey = '173e8e08-5826-c6a4-a17f-4be9d7c6d8b4';
        $message = <<<CIA
Received: from localhost (unknown [172.16.161.1])
	by asandberg (Postfix) with ESMTP id E2E9FE42F0
	for <ljsdf2323@sugarcrm.com>; Tue, 11 May 2010 15:15:50 -0700 (PDT)
Date: Tue, 11 May 2010 15:15:50 -0700
To: Random Sandberg <ljsdf2323@sugarcrm.com>
From: Administrator <asandberg@sugarcrm.com>
Reply-to: 
Subject: Pop Newsletter
Message-ID: <ddc9b4df5b9e4dcdacc4dac14471864c@localhost>
X-Priority: 3
X-Mailer: PHPMailer (phpmailer.codeworxtech.com) [version 2.3]
MIME-Version: 1.0
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset="UTF-8"

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.=
w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns=3D"http://www.w3.org/1999/xhtml">
<head>
=09<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3DUTF-8"=
 />
<title>Pop Newsletter</title>
</head>
<body><p>text stuff in here</p>
<p>&nbsp;</p>
<p><a href=3D"http://localhost/engineering/kobeAgain/sugarcrm/index.php?ent=
ryPoint=3Dremoveme&identifier=3D$trackerKey"> http=
://localhost/engineering/kobeAgain/sugarcrm/index.php?entryPoint=3Dremoveme=
&identifier=3D$trackerKey </a></p><br><IMG HEIGHT=
=3D'1' WIDTH=3D'1' src=3D'http://localhost/engineering/kobeAgain/sugarcrm/i=
ndex.php?entryPoint=3Dimage&identifier=3D173e8e08-5826-c6a4-a17f-4be9d7c6d8=
b4'></body></html>
CIA;
        $noteID = uniqid();
        $emailId = uniqid();
        $note = new Note();
        $note->description = $message;
        $note->file_mime_type = 'message/rfc822';
        $note->subject = 'Unit Test';
        $note->new_with_id = true;
        $note->id = $noteID;
        $note->email_id = $emailId;
        $note->email_type = 'Emails';
        $note->save();

        $email = new stdClass();
        $email->id = $emailId;
        $email->description = $message;
        $email->raw_source = $message;
        $email->date_created = gmdate('Y-m-d H:i:s');
        $logID = $this->createCampaignLogForTrackerKey($trackerKey);
        $email_header = new stdClass();
        $email->from_name = 'MAILER-DAEMON';
        $this->assertTrue(campaign_process_bounced_emails($email, $email_header), 'Unable to process bounced email');

        $GLOBALS['db']->query("DELETE FROM notes WHERE id='{$note->id}'");
        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id='{$logID}' OR target_tracker_key='{$trackerKey}'");
    }

    private function createCampaignLogForTrackerKey($trackerKey)
    {
        $l = new CampaignLog();
        $l->activity_type = 'targeted';
        $l->target_tracker_key = $trackerKey;
        $l->id = uniqid();
        $l->new_with_id = true;
        $l->save();
        return $l->id;
    }


    public static function gmailEmailData()
    {
        $trackerKey1 = '166da286-eb7a-207a-1987-4c3b8975aac9';
        $message1 = <<<CIA
Delivered-To: yellowandy@a17g.com
Received: by 10.231.191.75 with SMTP id dl11cs94110ibb;
        Mon, 12 Jul 2010 14:30:44 -0700 (PDT)
Received: by 10.142.140.18 with SMTP id n18mr17317756wfd.47.1278970243731;
        Mon, 12 Jul 2010 14:30:43 -0700 (PDT)
MIME-Version: 1.0
Return-Path: <>
Received: by 10.142.140.18 with SMTP id n18mr23095505wfd.47; Mon, 12 Jul 2010 
	14:30:43 -0700 (PDT)
From: Mail Delivery Subsystem <mailer-daemon@googlemail.com>
To: yellowandy@a17g.com
X-Failed-Recipients: =?UTF-8?B?77+9OGE3CgAAAElTTy04ODU5LTEANDhiMzc=?=
Subject: Delivery Status Notification (Failure)
Message-ID: <000e0cd185720e4c5b048b3777a4@google.com>
Date: Mon, 12 Jul 2010 21:30:43 +0000
Content-Type: text/plain; charset=ISO-8859-1
Content-Transfer-Encoding: quoted-printable

Delivery to the following recipient failed permanently:

     bademail444@sugarcrm.com

Technical details of permanent failure:=20
Google tried to deliver your message, but it was rejected by the recipient =
domain. We recommend contacting the other email provider for further inform=
ation about the cause of this error. The error that the other server return=
ed was: 550 550 5.1.1 <bademail444@sugarcrm.com>: Recipient address rejecte=
d: User unknown in relay recipient table (state 14).

----- Original message -----

Received: by 10.142.140.18 with SMTP id n18mr17317736wfd.47.1278970242744;
        Mon, 12 Jul 2010 14:30:42 -0700 (PDT)
Return-Path: <yellowandy@a17g.com>
Received: from localhost ([74.85.23.222])
        by mx.google.com with ESMTPS id 33sm5294224wfd.18.2010.07.12.14.30.=
41
        (version=3DTLSv1/SSLv3 cipher=3DRC4-MD5);
        Mon, 12 Jul 2010 14:30:42 -0700 (PDT)
Date: Mon, 12 Jul 2010 14:30:41 -0700
Return-Path: do_not_reply@example.com
To: Bad Email <bademail444@sugarcrm.com>
From: SugarCRM <yellowandy@a17g.com>
Reply-to:=20
Subject: Here is a campaign
Message-ID: <5c9983f10ef31604d73e89f7f62aca40@localhost>
X-Priority: 3
X-Mailer: PHPMailer (phpmailer.codeworxtech.com) [version 2.3]
X-CampTrackID: 166da286-eb7a-207a-1987-4c3b8975aac9
MIME-Version: 1.0
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset=3D"ISO-8859-1"

----- End of message -----

CIA;

        $trackerKey2 = '3d3e550e-0c61-ed2f-65fb-4c35855ef127';
        $message2 = <<<CIA
Delivered-To: oliver.sugar@gmail.com
Received: by 10.220.71.146 with SMTP id h18cs151678vcj;
        Thu, 8 Jul 2010 00:59:17 -0700 (PDT)
Received: by 10.142.144.2 with SMTP id r2mr3911830wfd.139.1278575956581;
        Thu, 08 Jul 2010 00:59:16 -0700 (PDT)
MIME-Version: 1.0
Return-Path: <>
Received: by 10.142.144.2 with SMTP id r2mr5262596wfd.139; Thu, 08 Jul 2010 
	00:59:16 -0700 (PDT)
From: Mail Delivery Subsystem <mailer-daemon@googlemail.com>
To: Oliver.Sugar@gmail.com
X-Failed-Recipients: =?UTF-8?B?aO+/ve+/vSkKAAAAdGV4dC9wbGFpbgA0OGFk?=
Subject: Delivery Status Notification (Failure)
Message-ID: <000e0cd3291cb5d999048adba9cc@google.com>
Date: Thu, 08 Jul 2010 07:59:16 +0000
Content-Type: text/plain; charset=ISO-8859-1
Content-Transfer-Encoding: quoted-printable

Delivery to the following recipient failed permanently:

     oliver5.sugar@gmail.com

Technical details of permanent failure:=20
Google tried to deliver your message, but it was rejected by the recipient =
domain. We recommend contacting the other email provider for further inform=
ation about the cause of this error. The error that the other server return=
ed was: 550 550-5.1.1 The email account that you tried to reach does not ex=
ist. Please try
550-5.1.1 double-checking the recipient's email address for typos or
550-5.1.1 unnecessary spaces. Learn more at                            =20
550 5.1.1 http://mail.google.com/support/bin/answer.py?answer=3D6596 c35si1=
5717477rvf.42 (state 14).

----- Original message -----

Received: by 10.142.144.2 with SMTP id r2mr3911827wfd.139.1278575956331;
        Thu, 08 Jul 2010 00:59:16 -0700 (PDT)
Return-Path: <oliver.sugar@gmail.com>
Received: from localhost ([58.246.70.178])
        by mx.google.com with ESMTPS id t11sm8244457wfc.4.2010.07.08.00.59.=
14
        (version=3DTLSv1/SSLv3 cipher=3DRC4-MD5);
        Thu, 08 Jul 2010 00:59:15 -0700 (PDT)
Date: Thu, 8 Jul 2010 15:59:06 +0800
Return-Path: oliver.sugar@gmail.com
To: testTarget02 <oliver5.sugar@gmail.com>
From: Oliver Sugar <oliver.sugar@gmail.com>
Reply-to:=20
Subject: test marketing001
Message-ID: <ca0d83bc421be94263211e98f8075575@localhost>
X-Priority: 3
X-Mailer: PHPMailer (phpmailer.codeworxtech.com) [version 2.3]
MIME-Version: 1.0
Content-Type: multipart/alternative;
	boundary=3D"b1_ca0d83bc421be94263211e98f8075575"

To remove yourself from this email list go to http://localhost/SugarEnt-Ful=
l-6.1.0beta/index.php?entryPoint=3Dremoveme&identifier=3D3d3e550e-0c61-ed2f=
-65fb-4c35855ef127

----- End Of message -----
CIA;
        $trackerKey3 = '2b8923e2-3d0c-baaa-354e-4c3eb5625533';
        $message3 = <<<CIA
Delivered-To: oliver.sugar@gmail.com
Received: by 10.220.98.79 with SMTP id p15cs154855vcn;
        Thu, 15 Jul 2010 00:15:46 -0700 (PDT)
Received: by 10.142.136.1 with SMTP id j1mr10395362wfd.325.1279178146208;
        Thu, 15 Jul 2010 00:15:46 -0700 (PDT)
MIME-Version: 1.0
Return-Path: <>
Received: by 10.142.136.1 with SMTP id j1mr14128331wfd.325; Thu, 15 Jul 2010 
	00:15:46 -0700 (PDT)
From: Mail Delivery Subsystem <mailer-daemon@googlemail.com>
To: Oliver.Sugar@gmail.com
X-Failed-Recipients: =?UTF-8?B?77+977+9JSQKAAAAdGV4dC9wbGFpbgA0OGI2?=
Subject: Delivery Status Notification (Failure)
Message-ID: <000e0cd32cc602596a048b67dfef@google.com>
Date: Thu, 15 Jul 2010 07:15:46 +0000
Content-Type: text/plain; charset=ISO-8859-1
Content-Transfer-Encoding: quoted-printable

Delivery to the following recipient failed permanently:

     oliver5.sugar@gmail.com

Technical details of permanent failure:=20
Google tried to deliver your message, but it was rejected by the recipient =
domain. We recommend contacting the other email provider for further inform=
ation about the cause of this error. The error that the other server return=
ed was: 550 550-5.1.1 The email account that you tried to reach does not ex=
ist. Please try
550-5.1.1 double-checking the recipient's email address for typos or
550-5.1.1 unnecessary spaces. Learn more at                            =20
550 5.1.1 http://mail.google.com/support/bin/answer.py?answer=3D6596 l5si18=
50473ybj.58 (state 14).

----- Original message -----

Received: by 10.142.136.1 with SMTP id j1mr10395353wfd.325.1279178145706;
        Thu, 15 Jul 2010 00:15:45 -0700 (PDT)
Return-Path: <oliver.sugar@gmail.com>
Received: from localhost ([58.246.70.178])
        by mx.google.com with ESMTPS id 33sm883411wfd.6.2010.07.15.00.15.43
        (version=3DTLSv1/SSLv3 cipher=3DRC4-MD5);
        Thu, 15 Jul 2010 00:15:44 -0700 (PDT)
Date: Thu, 15 Jul 2010 15:15:39 +0800
Return-Path: oliver.sugar@gmail.com
To: testTarget002 <oliver5.sugar@gmail.com>
From: oliver sugar <oliver.sugar@gmail.com>
Reply-to:=20
Subject: test marketing mail 001
Message-ID: <ca7bb1c154dbaf5e8c09cb38dbae2387@localhost>
X-Priority: 3
X-Mailer: PHPMailer (phpmailer.codeworxtech.com) [version 2.3]
X-CampTrackID: 2b8923e2-3d0c-baaa-354e-4c3eb5625533
MIME-Version: 1.0
Content-Type: multipart/alternative;
	boundary=3D"b1_ca7bb1c154dbaf5e8c09cb38dbae2387"

To remove yourself from this email list go to http://localhost/SugarEnt-Ful=
l-6.1.0beta/index.php?entryPoint=3Dremoveme&identifier=3D2b8923e2-3d0c-baaa=
-354e-4c3eb5625533
CIA;
        return [
            ['trackerKey' => $trackerKey1, 'message' => $message1],
            ['trackerKey' => $trackerKey2, 'message' => $message2],
            ['trackerKey' => $trackerKey3, 'message' => $message3],
        ];
    }
}
