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

require_once 'install/install_utils.php';
require_once 'modules/UpgradeWizard/uw_utils.php';

class Bug37214Test extends TestCase
{
    public $original_argv;
    public $has_original_config_si_file;
    public $current_working_dir;

    protected function setUp(): void
    {
        global $argv;
        if (isset($argv)) {
            $this->original_argv = $argv;
        }


        $this->current_working_dir = getcwd();

        $sugar_config_si = [
            'disable_count_query' => true,
            'external_cache_disabled_apc' => true,
            'external_cache_disabled_zend' => true,
            'external_cache_disabled_memcache' => true,
            'external_cache_disabled' => true,
        ];

        if (file_exists('config.php')) {
            copy('config.php', 'config.php.bug37214');
            include 'config.php';
            // remove items since merge_config_si_settings does not merge existing keys
            foreach ($sugar_config_si as $k => $v) {
                unset($sugar_config[$k]);
            }
            write_array_to_file('sugar_config', $sugar_config, 'config.php');
        }

        if (file_exists('config_si.php')) {
            $this->has_original_config_si_file = true;
            copy('config_si.php', 'config_si.php.bug37214');
        } else {
            $this->has_original_config_si_file = false;
            copy('config.php', 'config_si.php');
        }

        write_array_to_file('sugar_config_si', $sugar_config_si, 'config_si.php');
    }

    protected function tearDown(): void
    {
        if (isset($this->original_argv)) {
            global $argv;
            $argv = $this->original_argv;
        }

        if (file_exists('config.php.bug37214')) {
            copy('config.php.bug37214', 'config.php');
            unlink('config.php.bug37214');
        }

        if (file_exists($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php.bug37214')) {
            if ($this->has_original_config_si_file) {
                copy($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php.bug37214', $this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php');
            } else {
                unlink($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php');
            }
            unlink($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php.bug37214');
        } else {
            unlink($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php');
        }
    }


    public function test_silent_upgrade_parameters()
    {
        $sugar_config = [];
        if (!file_exists('config.php')) {
            $this->markTestSkipped('Unable to locate config.php file.  Skipping test.');
            return;
        }


        if (!file_exists($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php')) {
            $this->markTestSkipped('Unable to locate config_si.php file.  Skipping test.');
            return;
        }

        //Simulate silent upgrade arguments
        global $argv;
        $argv[0] = $this->current_working_dir . DIRECTORY_SEPARATOR . 'config.php'; //This would really be silentUpgrade.php, but this will suffice
        $argv[1] = $this->current_working_dir . DIRECTORY_SEPARATOR . 'someZipFile.php';
        $argv[2] = $this->current_working_dir . DIRECTORY_SEPARATOR . 'silent_upgrade.log';
        $argv[3] = $this->current_working_dir;
        $argv[4] = 'admin';

        $merge_result = merge_config_si_settings();

        include 'config.php';
        //echo var_export($sugar_config, true);
        $this->assertEquals(true, $sugar_config['disable_count_query'], 'Assert disable_count_query is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_apc'], 'Assert external_cache_disabled_apc is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_zend'], 'Assert external_cache_disabled_zend is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_memcache'], 'Assert external_cache_disabled_memcache is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled'], 'Assert external_cache_disabled is set to true.');
    }


    /**
     * test_silent_upgrade_parameters2
     * This is similar to test_silent_upgrade_parameters except that $argv[0] simulates the current directory
     * (imagine the caes of something like >php silentUpgrade.php xxx yyy zzz).  This is to prove that the
     * merge_config_si_settings() can correctly determine the presence of the config_si.php file given the
     * current directory.
     */
    public function test_silent_upgrade_parameters2()
    {
        $sugar_config = [];
        if (!file_exists('config.php')) {
            $this->markTestSkipped('Unable to locate config.php file.  Skipping test.');
            return;
        }


        if (!file_exists($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php')) {
            $this->markTestSkipped('Unable to locate config_si.php file.  Skipping test.');
            return;
        }

        //Simulate silent upgrade arguments
        global $argv;
        $argv[0] = 'config.php'; //This would really be silentUpgrade.php, but this will suffice
        $argv[1] = $this->current_working_dir . DIRECTORY_SEPARATOR . 'someZipFile.php';
        $argv[2] = $this->current_working_dir . DIRECTORY_SEPARATOR . 'silent_upgrade.log';
        $argv[3] = $this->current_working_dir;
        $argv[4] = 'admin';

        $merge_result = merge_config_si_settings(true);
        //$this->assertEquals(true, $merge_result, "Assert that we have merged values");

        include 'config.php';
        //echo var_export($sugar_config, true);
        $this->assertEquals(true, $sugar_config['disable_count_query'], 'Assert disable_count_query is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_apc'], 'Assert external_cache_disabled_apc is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_zend'], 'Assert external_cache_disabled_zend is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_memcache'], 'Assert external_cache_disabled_memcache is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled'], 'Assert external_cache_disabled is set to true.');
    }
}
