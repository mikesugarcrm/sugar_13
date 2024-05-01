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

require_once 'include/workflow/plugin_utils.php';

class Bug62487Test extends TestCase
{
    public $file = 'workflow/plugins/Bug62487Test/component_list.php';

    protected function setUp(): void
    {
        $this->file = create_custom_directory($this->file);

        $component_list = [
            'action' => [
                'listview' => [
                    'directory' => 'Bug62487Test',
                    'file' => 'Bug62487Test',
                    'class' => 'Bug62487Test',
                    'function' => 'bug62487test_listview',
                ],
            ],
        ];

        write_array_to_file('component_list', $component_list, $this->file);
    }

    protected function tearDown(): void
    {
        rmdir_recursive(dirname($this->file));
    }

    public function testPluginListArrayKeys()
    {
        $list = extract_plugin_list();

        $this->assertArrayHasKey('action', $list);
        $this->assertArrayHasKey('listview', $list['action']);
        $this->assertArrayHasKey('Bug62487Test', $list['action']['listview'], 'Bug62487Test Failed Workflow Plugins are incorrect');
    }
}
