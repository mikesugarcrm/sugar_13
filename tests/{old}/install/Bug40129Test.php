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

class Bug40129Test extends TestCase
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

        if (file_exists('config_si.php')) {
            $this->has_original_config_si_file = true;
            copy('config_si.php', 'config_si.php.bug40129');
        } else {
            $this->has_original_config_si_file = false;
            copy('config.php', 'config_si.php');
        }

        $sugar_config_si = [
            'setup_db_host_name' => 'localhost',
            'setup_db_database_name' => 'pineapple',
            'setup_db_drop_tables' => 0,
            'setup_db_create_database' => 1,
            'setup_db_pop_demo_data' => false,
            'setup_site_admin_user_name' => 'admin',
            'setup_site_admin_password' => 'a',
            'setup_db_create_sugarsales_user' => 0,
            'setup_db_admin_user_name' => 'root',
            'setup_db_admin_password' => '',
            'setup_db_sugarsales_user' => 'root',
            'setup_db_sugarsales_password' => '',
            'setup_db_type' => 'mysql',
            'setup_license_key_users' => 100,
            'setup_license_key_expire_date' => '2010-12-25',
            'setup_license_key' => 'internal sugar user 20100224',
            'setup_site_url' => 'http://localhost/pineapple/build/rome/builds/ent/sugarcrm',
            'setup_system_name' => 'pineapple',
            'default_currency_iso4217' => 'USD',
            'default_currency_name' => 'US Dollars',
            'default_currency_significant_digits' => '2',
            'default_currency_symbol' => '$',
            'default_date_format' => 'Y-m-d',
            'default_time_format' => 'H:i',
            'default_decimal_seperator' => '.',
            'default_export_charset' => 'ISO-8859-1',
            'default_language' => 'en_us',
            'default_locale_name_format' => 's f l',
            'default_number_grouping_seperator' => ',',
            'export_delimiter' => ',',

            //These are the additional configuration values we are really testing
            'disable_count_query' => true,
            'external_cache_disabled_apc' => true,
            'external_cache_disabled_zend' => true,
            'external_cache_disabled_memcache' => true,
            'external_cache_disabled' => true,
        ];

        write_array_to_file('sugar_config_si', $sugar_config_si, 'config_si.php');

        if (file_exists('config.php')) {
            copy('config.php', 'config.php.bug40129');

            include 'config.php';
            // remove items since merge_config_si_settings does not merge existing keys
            foreach ($sugar_config_si as $k => $v) {
                unset($sugar_config[$k]);
            }
            write_array_to_file('sugar_config', $sugar_config, 'config.php');
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->original_argv)) {
            global $argv;
            $argv = $this->original_argv;
        }

        if (file_exists('config.php.bug40129')) {
            copy('config.php.bug40129', 'config.php');
            unlink('config.php.bug40129');
        }

        if (file_exists($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php.bug40129')) {
            if ($this->has_original_config_si_file) {
                copy($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php.bug40129', $this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php');
            } else {
                unlink($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php');
            }
            unlink($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php.bug40129');
        } else {
            unlink($this->current_working_dir . DIRECTORY_SEPARATOR . 'config_si.php');
        }
    }


    public function test_silent_install()
    {
        $sugar_config = [];
        if (!file_exists('config.php')) {
            $this->markTestSkipped('Unable to locate config.php file.  Skipping test.');
            return;
        }


        if (!file_exists('config_si.php')) {
            $this->markTestSkipped('Unable to locate config_si.php file.  Skipping test.');
            return;
        }

        $merge_result = merge_config_si_settings(false, 'config.php', 'config_si.php');

        include 'config.php';
        //echo var_export($sugar_config, true);
        $this->assertEquals(true, $sugar_config['disable_count_query'], 'Assert disable_count_query is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_apc'], 'Assert external_cache_disabled_apc is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_zend'], 'Assert external_cache_disabled_zend is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled_memcache'], 'Assert external_cache_disabled_memcache is set to true.');
        $this->assertEquals(true, $sugar_config['external_cache_disabled'], 'Assert external_cache_disabled is set to true.');

        $this->assertTrue(!isset($sugar_config['setup_site_admin_user_name']), 'Assert setup_site_admin_user_name is not added to config.php.');
        $this->assertTrue(!isset($sugar_config['setup_site_admin_password']), 'Assert setup_site_admin_password is not added to config.php.');
    }
}
