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


class RestMetadataPartialTest extends RestTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testMetadataGetHashes()
    {
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata?only_hash=true');

        $this->assertTrue(isset($restReply['reply']['modules']['Accounts']['_hash']), 'Account module hash is missing. Reply looked like: ' . var_export($restReply['replyRaw'], true));
        $this->assertFalse(isset($restReply['reply']['modules']['Accounts']['fields']), 'Account module has fields.');
    }

    /**
     * @group rest
     */
    public function testMetadataPartialGetModules()
    {
        // Fetch just the hashes
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata?only_hash=true&type_filter=modules&module_filter=Accounts');

        $this->assertTrue(isset($restReply['reply']['modules']['Accounts']['_hash']), 'Account module only hash is missing.');

        // Call with the same set of hashes that we were sent
        $goodHashes = ['modules' => ['Accounts' => $restReply['reply']['modules']['Accounts']['_hash']]];
        $restReply2 = $this->restCall('metadata?type_filter=modules&module_filter=Accounts', json_encode($goodHashes));

        $this->assertFalse(isset($restReply2['reply']['modules']['Accounts']['fields']), 'Account module fields were returned when the hashes matched.');

        // Mess up the hashes
        $badHashes = ['modules' => ['Accounts' => 'BAD HASH, NO SOUP FOR YOU']];

        $this->clearMetadataCache();
        $restReply3 = $this->restCall('metadata?type_filter=modules&module_filter=Accounts', json_encode($badHashes));

        $this->assertTrue(isset($restReply3['reply']['modules']['Accounts']['fields']), 'Account module fields were not returned when the hashes didn\'t match.');
    }
}
