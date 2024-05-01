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

/***
 * Test cases for Bug 44113
 */
class Bug44113Test extends TestCase
{
    //@codingStandardsIgnoreStart
    public $_original_email_xss;
    //@codingStandardsIgnoreEnd
    private $cfg; // configurator
    private $emailMan;
    private $email_xss; // the security settings to be saved in config_ovverride
    private $original_email_xss = null;

    protected function setUp(): void
    {
        $sugar_config = [];
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->is_admin = '1';

        OutboundEmailConfigurationTestHelper::setUp();

        require 'config.php';

        if (isset($sugar_config['email_xss'])) {
            $this->_original_email_xss = $sugar_config['email_xss'];
            $this->cfg = new Configurator();
            $this->cfg->config['email_xss'] = getDefaultXssTags();
            $this->cfg->handleOverride();
        }

        // email_xss settings to be saved using config_override
        $this->email_xss = [
            //'applet' => 'applet',
            'form' => 'form',
            'iframe' => 'iframe',
            'script' => 'script',
        ];
    }

    protected function tearDown(): void
    {
        if (isset($this->original_email_xss)) {
            $this->cfg = new Configurator();
            $this->cfg->config['email_xss'] = $this->original_email_xss;
            $this->cfg->handleOverride();
        }

        unset($this->cfg);
        unset($this->emailMan);
        unset($this->email_xss);
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testEmailManController()
    {
        global $sugar_config;
        $conn = new EmailManController();

        // make sure we preserve the System Outbound Email Configuration
        $q = "SELECT * FROM outbound_email WHERE type = 'system'";
        $r = $GLOBALS['db']->query($q);
        $a = $GLOBALS['db']->fetchByAssoc($r);

        if (!empty($a['id'])) {
            foreach ($a as $col => $val) {
                $_REQUEST[$col] = $_POST[$col] = $val;
            }
        }

        // populate the REQUEST and POST arrays which are referenced when writing config_override
        foreach ($this->email_xss as $key => $val) {
            $_REQUEST[$key] = $_POST[$key] = $val;
        }

        $new_security_settings = $this->email_xss;

        // make sure that settings from config.php are untouched
        $original_security_settings = getDefaultXssTags();
        $this->assertNotEquals(
            $original_security_settings,
            $new_security_settings,
            'ensure that original email_xss is not touched'
        );

        $conn->action_Save(); // testing the save,
        // it should use the above request vars
        // to create a new config_override.php

        // now check to make sure that config_override received the updated settings
        require 'config_override.php';
        $this->assertEquals(
            $new_security_settings,
            $sugar_config['email_xss'],
            'testing that new email_xss settings got saved'
        );
    }

    /**
     * make sure that new configs are saved using handleOverride
     */
    public function testSavingToConfigOverride()
    {
        $this->cfg = new Configurator();
        global $sugar_config;

        $new_security_settings = $this->email_xss;

        $this->cfg->config['email_xss'] = $new_security_settings;
        $this->cfg->handleOverride();

        // just test to make sure that configuration is saved
        $this->assertEquals($sugar_config['email_xss'], $new_security_settings, 'testing configurator');
    }
}
