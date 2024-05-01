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

class SugarPortalModuleTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', [true, true]);
    }

    protected function tearDown(): void
    {
        $file = 'custom/modules/Opportunities/clients/portal/views/record/record.php';
        if (file_exists($file)) {
            unlink($file);
        }

        $dirs = [
            'custom/modules/Opportunities/clients/portal/views/record',
            'custom/modules/Opportunities/clients/portal/views',
            'custom/modules/Opportunities/clients/portal',
        ];
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }

        SugarTestHelper::tearDown();
    }

    /**
     * @covers \SugarPortalModule::__construct
     */
    public function testConstructCases()
    {
        $spm = new \SugarPortalModule('Cases');

        $expectedViews = [
            'record.php',
            'list.php',
        ];

        $spmViews = array_keys($spm->views);
        sort($spmViews);
        sort($expectedViews);
        $this->assertEquals($spmViews, $expectedViews);
    }

    /**
     * @covers \SugarPortalModule::__construct
     */
    public function testConstructCustomOpportunities()
    {
        mkdir_recursive('custom/modules/Opportunities/clients/portal/views/record');
        touch('custom/modules/Opportunities/clients/portal/views/record/record.php');

        $spm = new \SugarPortalModule('Opportunities');
        $this->assertContains('record.php', array_keys($spm->views), 'record.php should be part of this list: ' . print_r(array_keys($spm->views), true));
    }
}
