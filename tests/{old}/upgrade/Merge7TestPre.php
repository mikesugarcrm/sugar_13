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

class Merge7TestPre extends UpgradeTestCase
{
    protected $new_dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->new_dir = sugar_cached('merge7test');
        mkdir_recursive($this->new_dir);
        $this->upgrader->context['new_source_dir'] = $this->new_dir;
        $this->upgrader->setVersions('7.0.0', 'ent', '7.2.0', 'ent');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        rmdir_recursive('modules/Accounts/clients/test');
        rmdir_recursive('custom/modules/Accounts/clients/test');
    }


    protected function createView($viewname, $data, $prefix = '')
    {
        $filename = "modules/Accounts/clients/test/views/$viewname/$viewname.php";
        if ($prefix) {
            $filename = "$prefix/$filename";
        }
        mkdir_recursive(dirname($filename));
        SugarTestHelper::saveFile($filename);
        write_array_to_file("viewdefs['Accounts']['test']['view']['$viewname']", $data, $filename);
    }

    /**
     * Test for Merge7
     */
    public function testMerge7Pre()
    {
        $data = [
            'panels' => [
                [
                    'name' => 'panel_hidden',
                    'fields' => ['email', 'phone', 'fax'],
                ],
            ],
        ];
        $data2 = [
            'panels' => [
                [
                    'name' => 'panel_hidden',
                    'fields' => ['email', 'fax', 'description'],
                ],
            ],
        ];
        $data3 = [];

        // no custom, no update
        $this->createView('test1', $data);
        // update, no custom
        $this->createView('test2', $data);
        $this->createView('test2', $data2, $this->new_dir);
        // custom, old data
        $this->createView('test3', $data);
        $this->createView('test3', $data2, $this->new_dir);
        $this->createView('test3', $data, 'custom');
        // update, custom, no data
        $this->createView('test41', $data3);
        $this->createView('test41', $data, $this->new_dir);
        $this->createView('test41', $data2, 'custom');
        $this->createView('test42', $data);
        $this->createView('test42', $data3, $this->new_dir);
        $this->createView('test42', $data2, 'custom');
        $this->createView('test43', $data);
        $this->createView('test43', $data, $this->new_dir);
        $this->createView('test44', $data3, 'custom');
        // update, custom, new data
        $this->createView('test5', $data);
        $this->createView('test5', $data2, $this->new_dir);
        $this->createView('test5', $data2, 'custom');


        $script = $this->upgrader->getScript('pre', 'Merge7');
        $script->run();

        $this->assertEquals(['modules/Accounts/clients/test/views/test3/test3.php'], array_keys($this->upgrader->state['for_merge']));
    }
}
