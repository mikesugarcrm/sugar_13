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


class RestClearCacheTest extends RestTestBase
{
    private $customFile = 'custom/clients/base/api/PongApi.php';
    private $customDirMade = false;

    protected function tearDown(): void
    {
        if (file_exists($this->customFile)) {
            unlink($this->customFile);
        }
    }

    /**
     * @group rest
     */
    public function testCache()
    {
        // This needs to be called before the custom dir is made
        $replyPing = $this->restCall('ping');
        $this->assertEquals('pong', $replyPing['reply']);

        if (!is_dir('custom/clients/base/api')) {
            $this->customDirMade = true;
            SugarAutoLoader::ensureDir('custom/clients/base/api');
        }

        // Preapre the custom file
        $file_contents = <<<EOQ
<?php
class PongApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'pong' => array(
                'reqType' => 'GET',
                'path' => array('ping'),
                'pathVars' => array(''),
                'method' => 'pong',
                'shortHelp' => 'An example API only responds with ping',
                'longHelp' => 'include/api/html/ping_base_help.html',
            ),
            );
    }
    public function pong() {
        return 'ping';
    }
}
EOQ;
        file_put_contents($this->customFile, $file_contents);
        // verify ping
        // verify pong isn't there
        $replyPong = $this->restCall('ping');
        $this->assertNotEquals('ping', $replyPong['reply'], 'Wrong reply: ' . var_export($replyPong, true));

        // run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $GLOBALS['current_user'] = $user->getSystemUser();

        $_REQUEST['repair_silent'] = 1;
        $rc = new RepairAndClear();
        $rc->clearAdditionalCaches();
        $GLOBALS['current_user'] = $old_user;

        $this->assertTrue(!file_exists('cache/include/api/ServiceDictionary.rest.php'), "Didn't really clear the cache");


        // verify pong is there now
        $replyPong = $this->restCall('ping');
        $this->assertEquals('ping', $replyPong['reply']);

        // Now undo it all and test again
        // Clean up after ourselves
        if (file_exists($this->customFile)) {
            $dirname = dirname($this->customFile);
            unlink($this->customFile);

            if ($this->customDirMade) {
                $done = rmdir($dirname);
            }
        }

        // run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $GLOBALS['current_user'] = $user->getSystemUser();

        $_REQUEST['repair_silent'] = 1;
        $rc = new RepairAndClear();
        $rc->clearAdditionalCaches();
        $GLOBALS['current_user'] = $old_user;

        $this->assertTrue(!file_exists('cache/include/api/ServiceDictionary.rest.php'), "Didn't really clear the cache the SECOND time");

        // verify pong isn't there
        $replyPong = $this->restCall('ping');
        $this->assertEquals('pong', $replyPong['reply']);
    }
}
