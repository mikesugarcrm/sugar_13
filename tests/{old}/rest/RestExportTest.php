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


class RestExportTest extends RestTestBase
{
    // for now, export and MassExport use the same rest endpoints. This will change.
    private $singleRestPath = 'Accounts/export';
    private $massRestPath = 'Accounts/export';

    protected function setUp(): void
    {
        parent::setUp();
        // multiple uids
        $num_accounts = 25;
        for ($i = 0; $i < $num_accounts; $i++) {
            $this->accounts[] = SugarTestAccountUtilities::createAccount();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanUpRecords();
        $this->accounts = [];
    }

    public function testExportWithFilter()
    {
        // FIXME TY-1314: investigate why this test fails
        $chosenIndex = 13;
        // this filter should retrieve only one account.
        $reply = $this->restCall($this->massRestPath . '?filter=' . urlencode('[{"name":"' . $this->accounts[$chosenIndex]->name . '"}]'));
        foreach ($this->accounts as $i => $account) {
            if ($i == $chosenIndex) {
                $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain chosen account ' . $i . ' ' . $account->name);
            } else {
                $this->assertNotContains($account->name, $reply['replyRaw'], 'Reply contains non-chosen account ' . $i . ' ' . $account->name);
            }
        }
    }

    public function testExportWithoutFilter()
    {
        // FIXME TY-1314: investigate why this test fails
        $reply = $this->restCall($this->massRestPath);

        // we want them all.
        foreach ($this->accounts as $i => $account) {
            $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain account ' . $i . ' ' . $account->name);
        }
    }

    public function testExportSample()
    {
        // FIXME TY-1314: investigate why this test fails
        $reply = $this->restCall($this->massRestPath . '?sample=true$all=true');
        $this->assertContains('This is a sample import file', $reply['replyRaw'], 'Reply does not contain description text');
    }


    /**
     * this test is to make sure our rest call can handle a GET arg in array format
     */
    public function testExportWithUids()
    {
        // FIXME TY-1314: investigate why this test fails
        // single uid as array
        $chosenIndex = 17;
        $reply = $this->restCall($this->massRestPath . '?uid[]=' . $this->accounts[$chosenIndex]->id);
        foreach ($this->accounts as $i => $account) {
            if ($i == $chosenIndex) {
                $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain chosen account ' . $i . ' ' . $account->name);
            } else {
                $this->assertNotContains($account->name, $reply['replyRaw'], 'Reply contains non-chosen account ' . $i . ' ' . $account->name);
            }
        }

        // multiple uids - emulate jQuery's $.param() method, which is used by sugarapi.js::buildURL
        // called as $.param({uid: [a,b,c]})
        // http://api.jquery.com/jQuery.param/
        // we only want to retrieve accounts 0..$chosen_index-1 -- guard against case where all accounts are retrieved indiscriminately
        $accountString = '';
        for ($i = 0; $i < $chosenIndex; $i++) {
            $accountString .= 'uid[]=' . urlencode($this->accounts[$i]->id) . '&';
        }
        $accountString = rtrim($accountString, '&');

        $reply = $this->restCall($this->massRestPath . '?' . $accountString);
        foreach ($this->accounts as $i => $account) {
            if ($i < $chosenIndex) {
                $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain chosen account ' . $i . ' ' . $account->name);
            } else {
                $this->assertNotContains($account->name, $reply['replyRaw'], 'Reply contains non-chosen account ' . $i . ' ' . $account->name);
            }
        }
    }
}
