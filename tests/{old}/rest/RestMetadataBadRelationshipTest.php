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

/**
 * Bug 57782 and 57780
 */
class RestMetadataBadRelationshipTest extends RestTestBase
{
    /**
     * @var mixed|string[]
     */
    public $files;

    protected function tearDown(): void
    {
        $GLBOALS = [];
        // delete file
        foreach ($this->files as $file) {
            unlink($file);
        }
        // re-run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();

        $GLOBALS['current_user'] = $user->getSystemUser();

        // run repair and rebuild
        $_REQUEST['repair_silent'] = 1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(['clearAll'], ['Accounts'], false, false);

        // switch back to the user
        $GLBOALS['current_user'] = $old_user;
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testBadRelationship()
    {
        $GLBOALS = [];
        /**
         * For full suite runs immediately after installation (like for CI), the
         * relationship cache will have already been created. The cache needs to
         * be cleared prior to this run so that this gets picked up properly for
         * testing;
         */
        SugarRelationshipFactory::deleteCache();

        // write out a bad relationship vardef
        $metadata = '<?php
$dictionary[\'Account\'][\'fields\'][\'notes\'][\'relationship\'] = "accounts_notes_awesome";
';

        $metadata_dir = 'custom/Extension/modules/Accounts/Ext/Vardefs';
        $metadata_file = 'accounts_notes_field.php';
        if (!is_dir($metadata_dir)) {
            mkdir("{$metadata_dir}", 0777, true);
        }

        file_put_contents($metadata_dir . '/' . $metadata_file, $metadata);

        $this->assertTrue(file_exists($metadata_dir . '/' . $metadata_file), 'Did not write out the new cache file');

        $this->files[] = $metadata_dir . '/' . $metadata_file;

        // run repair and rebuild
        // save old user
        $old_user = $GLOBALS['current_user'];
        $user = new User();

        $GLOBALS['current_user'] = $user->getSystemUser();


        // run repair and rebuild
        $_REQUEST['repair_silent'] = 1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(['clearAll'], ['Accounts'], false, false);

        // switch back to the user
        $GLBOALS['current_user'] = $old_user;

        // call module metadata
        $this->restCall('metadata/flush', 'flush');
        $restReply = $this->restCall('metadata?type_filter=modules');

        // verify no 500 and results for the module
        $this->assertNotEquals($restReply['info']['http_code'], 500, 'HTTP Code is 500');

        $this->assertTrue(isset($restReply['reply']['modules']['Accounts']), 'Account module is missing. Reply looked like: ' . var_export($restReply['replyRaw'], true));

        $this->assertEquals($restReply['reply']['modules']['Accounts']['fields']['notes']['relationship'], 'accounts_notes_awesome', 'Did not rewrite relationship to accounts_notes_awesome, it is: ' . $restReply['reply']['modules']['Accounts']['fields']['notes']['relationship']);
    }
}
