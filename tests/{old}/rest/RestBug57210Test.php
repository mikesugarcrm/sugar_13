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
 * Bug 57210:
 * Need to be able to mark a related record 'deleted=1' when a file uploads fails.
 * delete_if_fails flag is an optional query string which can trigger this behavior. An example
 * use case might be: user's in a modal and client: 1. POST's related record 2. uploads file...
 * If the file was too big, the user may still want to go back and select a smaller file < max;
 * but now, upon saving, the client will attempt to PUT related record first and if their ACL's
 * may prevent edit/deletes it would fail. This rectifies such a scenario.
 */
class RestBug57210Test extends RestFileTestBase
{
    private $configOverrideExisted = false;
    private $configOverrideName = 'config_override.php';

    protected function setUp(): void
    {
        $sugar_config = [];
        parent::setUp();

        // Hijack the config_override.php file if exists, otherwise we'll create sugar_config anew
        if (file_exists($this->configOverrideName)) {
            require $this->configOverrideName;
            rename($this->configOverrideName, ($this->configOverrideName . '.bak'));
            $this->configOverrideExisted = true;
        } else {
            $this->configOverrideExisted = false;
        }
        $sugar_config['upload_maxsize'] = '1';

        // write_array_to_file will write array like $foo = array(...) which is NOT what
        // we want here since it will overwrite the global! So we build line by line.
        $newContents = "<?php\n";
        foreach ($sugar_config as $key => $value) {
            $newContents .= override_value_to_string_recursive2('sugar_config', $key, $value);
        }
        file_put_contents($this->configOverrideName, $newContents);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // If was original config override, copy back over original kept in our ".bak"
        if ($this->configOverrideExisted && file_exists($this->configOverrideName . '.bak')) {
            rename(($this->configOverrideName . '.bak'), $this->configOverrideName);
        } else {
            // If it didn't exist before, we need to remove the one we created
            if (file_exists($this->configOverrideName)) {
                unlink($this->configOverrideName);
            }
        }
    }

    /**
     * @group rest
     */
    public function testSimulateFileTooLargeWithDeleteIfFails()
    {
        $fileToPost = ['filename' => '@include/images/badge_256.png'];
        $reply = $this->restCall('Notes/' . $this->note_id . '/file/filename' . '?delete_if_fails=true', $fileToPost, 'POST');

        // Check DB to see if the related Note actually got marked deleted
        $ret = $GLOBALS['db']->query("SELECT deleted from notes where id = '" . $this->note_id . "'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);

        // Our main expectation is that the related Note record got marked deleted=1
        $this->assertEquals(1, intval($row['deleted']), 'Expected deleted column to be marked 1');
        $this->assertArrayHasKey('error', $reply['reply'], 'No error message returned');
        $this->assertEquals('fatal_error', $reply['reply']['error'], 'Expected error string not returned');
        $this->assertContains('ERROR: uploaded file was too big', $reply['reply']['error_message'], 'Expected error message not returned');
    }

    /**
     * @group rest
     */
    public function testSimulateFileTooLargeWithOutDeleteIfFails()
    {
        $fileToPost = ['filename' => '@include/images/badge_256.png'];
        $reply = $this->restCall('Notes/' . $this->note_id . '/file/filename', $fileToPost, 'POST');

        // Check DB to ensure that the related Note did NOT got marked deleted
        $ret = $GLOBALS['db']->query("SELECT deleted from notes where id = '" . $this->note_id . "'", true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);

        // Our main expectation is that the related Note record did NOT get marked as deleted (e.g. deleted=0)
        $this->assertEquals(0, intval($row['deleted']), 'Expected deleted column to be marked 0 (not deleted)');
        $this->assertArrayHasKey('error', $reply['reply'], 'No error message returned');
        $this->assertEquals('fatal_error', $reply['reply']['error'], 'Expected error string not returned');
        $this->assertContains('ERROR: uploaded file was too big', $reply['reply']['error_message'], 'Expected error message not returned');
    }
}
