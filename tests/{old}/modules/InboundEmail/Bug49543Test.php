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

/**
 * Bug #49543
 * Email cache reset issue causing email deletion slowness
 * @ticket 49543
 */
class Bug49543Test extends TestCase
{
    private function createMail($subj, $from, $to, $imap_uid, $date, $uid)
    {
        $mail = new stdClass();
        $mail->subject = $subj;
        $mail->from = $from;
        $mail->to = $to;
        $mail->imap_uid = $imap_uid;
        $mail->date = $date;
        $mail->message_id = '';
        $mail->size = '1234';
        $mail->uid = $uid;
        $mail->msgno = 0;
        $mail->recent = 0;
        $mail->flagged = 0;
        $mail->answered = 0;
        $mail->deleted = 0;
        $mail->draft = 0;
        $mail->seen = 1;

        return $mail;
    }

    /**
     * @group 49543
     */
    public function testSetCacheValue()
    {
        $insert = [];
        global $timedate;

        $ie_id = '123';
        $mailbox = 'trash';
        $time = random_int(0, mt_getrandmax());
        $subj = 'test ' . $time;
        $GLOBALS['db']->query(sprintf(
            "INSERT INTO email_cache (ie_id, mbox, subject, fromaddr, toaddr, imap_uid) 
                                VALUES ('%s', '%s', '%s', 'from@test.com', 'to@test.com', '11')",
            $ie_id,
            $mailbox,
            $subj
        ));

        //deleted item from inbox which will be inserted in trash
        $insert[0] = $this->createMail($subj . '_new', 'from@test.com', 'to@test.com', '12', '2012-11-11 11:11:11', '12');

        //old trash item which should be updated
        $insert[1] = $this->createMail($subj . '_old', 'from@test.com', 'to@test.com', '11', '2011-11-11 11:11:11', '11');

        $ie = new InboundEmail();
        $ie->id = $ie_id;

        $ie->setCacheValue($mailbox, $insert);

        $fr = $GLOBALS['db']->fetchRow($GLOBALS['db']->query("SELECT subject FROM email_cache WHERE imap_uid = '11'"));

        //if old trash item was updated successfully then 'subject' has new value
        $this->assertTrue($fr['subject'] == $subj . '_old');

        $GLOBALS['db']->query(sprintf("DELETE FROM email_cache WHERE mbox = '%s'", $mailbox));
    }
}
