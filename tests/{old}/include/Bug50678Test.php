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

class Bug50678Test extends TestCase
{
    private $backupConfig;

    protected function setUp(): void
    {
        global $sugar_config;

        $this->backupConfig = $sugar_config;

        if (!empty($sugar_config['custom_help_url'])) {
            unset($sugar_config['custom_help_url']);
        }
        if (!empty($sugar_config['custom_help_base_url'])) {
            unset($sugar_config['custom_help_base_url']);
        }
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        $sugar_config = $this->backupConfig;
    }

    public function testGetDefaultHelpURL()
    {
        global $sugar_config;

        $this->assertSame(
            'https://www.sugarcrm.com/crm/product_doc.php?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6',
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6')
        );
        $this->assertSame(
            'https://www.sugarcrm.com/crm/product_doc.php?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6&anchor=arg7',
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7')
        );

        $this->assertSame(
            'https://www.sugarcrm.com/crm/product_doc.php?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6&anchor=arg7&products=arg8',
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7', 'arg8')
        );
    }

    public function testGetCustomHelpURL()
    {
        global $sugar_config;

        $url = 'http://example.com';

        $sugar_config['custom_help_url'] = $url;

        $this->assertSame($url, get_help_url());
        $this->assertSame($url, get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6'));
        $this->assertSame($url, get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7'));
    }

    public function testGetCustomBaseHelpURL()
    {
        global $sugar_config;

        $url = 'http://example.com';

        $sugar_config['custom_help_base_url'] = $url;

        $this->assertSame(
            $url . '?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6',
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6')
        );
        $this->assertSame(
            $url . '?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6&anchor=arg7',
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7')
        );
    }
}
