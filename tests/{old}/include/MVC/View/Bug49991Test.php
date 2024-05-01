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

/**
 * Bug49991Test.php
 * @author Collin Lee
 *
 * This test will check the enhancements made so that we may better load custom files.  While the bug was
 * originally filed for the Connectors module, this change was applied to the SugarView layer to allow all
 * views to take advantage of not having to repeatedly check the custom directory for the presence of a file.
 */
class Bug49991Test extends TestCase
{
    public $mock;
    public $sourceBackup;

    protected function setUp(): void
    {
        $this->mock = new Bug49991SugarViewMock();
        mkdir_recursive('custom/modules/Connectors/tpls');
        if (file_exists('custom/modules/Connectors/tpls/source_properties.tpl')) {
            $this->sourceBackup = file_get_contents('custom/modules/Connectors/tpls/source_properties.tpl');
        }
        copy('modules/Connectors/tpls/source_properties.tpl', 'custom/modules/Connectors/tpls/source_properties.tpl');
    }

    protected function tearDown(): void
    {
        if (!empty($this->sourceBackup)) {
            file_put_contents('custom/modules/Connectors/tpls/source_properties.tpl', $this->sourceBackup);
        } else {
            unlink('custom/modules/Connectors/tpls/source_properties.tpl');
        }
        unset($this->mock);
    }

    /**
     * testGetCustomFilePathIfExists
     *
     * Simple test just to assert that we have found the custom file
     */
    public function testGetCustomFilePathIfExists()
    {
        $this->assertEquals('custom/modules/Connectors/tpls/source_properties.tpl', $this->mock->getCustomFilePathIfExistsTest('modules/Connectors/tpls/source_properties.tpl'), 'Could not find the custom tpl file');
    }
}

class Bug49991SugarViewMock extends SugarView
{
    public function getCustomFilePathIfExistsTest($file)
    {
        return $this->getCustomFilePathIfExists($file);
    }
}
