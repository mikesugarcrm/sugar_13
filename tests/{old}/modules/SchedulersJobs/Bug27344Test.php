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

class Bug27344Test extends TestCase
{
    private $initialServerPort;
    private $hasInitialServerPort;

    protected function setUp(): void
    {
        $this->hasInitialServerPort = isset($_SERVER['SERVER_PORT']);
        if ($this->hasInitialServerPort) {
            $this->initialServerPort = $_SERVER['SERVER_PORT'];
        }
    }

    protected function tearDown(): void
    {
        if ($this->hasInitialServerPort) {
            $_SERVER['SERVER_PORT'] = $this->initialServerPort;
        } else {
            unset($_SERVER['SERVER_PORT']);
        }
    }

    public function testLocalServerPortNotUsed()
    {
        $url = $GLOBALS['sugar_config']['site_url'] . '/maintenance.php';

        $_SERVER['SERVER_PORT'] = '9090';
        $sJob = new SchedulersJob(false);
        $this->assertTrue($sJob->fireUrl($url));
    }
}
