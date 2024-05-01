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
 * Bug50431Test.php
 *
 * This file tests the getMappingClassName function in modules/Import/views/view.step3.php
 */
class Bug50431Test extends TestCase
{
    private $customMappingFile = 'custom/modules/Import/maps/ImportMapCustomTestImportToken.php';
    private $customMappingFile2 = 'custom/modules/Import/maps/ImportMapTestImportToken.php';
    private $customMappingFile3 = 'custom/modules/Import/maps/ImportMapOther.php';
    private $outOfBoxTestFile = 'modules/Import/maps/ImportMapTestImportToken.php';
    private $source = 'TestImportToken';

    protected function setUp(): void
    {
        if (!is_dir('custom/modules/Import/maps')) {
            mkdir_recursive('custom/modules/Import/maps');
        }

        file_put_contents($this->customMappingFile, '<?php class ImportMapCustomTestImportToken { } ');
        file_put_contents($this->customMappingFile2, '<?php class ImportMapTestImportToken { } ');
        file_put_contents($this->customMappingFile3, '<?php class ImportMapOther { } ');
        file_put_contents($this->outOfBoxTestFile, '<?php class ImportMapTestImportTokenOutOfBox { } ');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->customMappingFile)) {
            unlink($this->customMappingFile);
        }

        if (file_exists($this->customMappingFile2)) {
            unlink($this->customMappingFile2);
        }

        if (file_exists($this->customMappingFile3)) {
            unlink($this->customMappingFile3);
        }

        if (file_exists($this->outOfBoxTestFile)) {
            unlink($this->outOfBoxTestFile);
        }
    }

    public function testGetMappingClassName()
    {
        $view = new Bug50431ImportViewStep3Mock();
        $result = $view->getMappingClassName($this->source);
        $this->assertEquals('ImportMapCustomTestImportToken', $result, 'Failed to load ' . $this->customMappingFile);

        unlink($this->customMappingFile);
        $result = $view->getMappingClassName($this->source);
        $this->assertEquals('ImportMapTestImportToken', $result, 'Failed to load ' . $this->customMappingFile2);

        unlink($this->customMappingFile2);
        $result = $view->getMappingClassName($this->source);
        $this->assertEquals('ImportMapTestImportToken', $result, 'Failed to load ' . $this->outOfBoxTestFile);

        unlink($this->outOfBoxTestFile);
        $result = $view->getMappingClassName($this->source);
        $this->assertEquals('ImportMapOther', $result, 'Failed to load ' . $this->customMappingFile3);
    }
}


class Bug50431ImportViewStep3Mock extends ImportViewStep3
{
    public function getMappingClassName($source)
    {
        return parent::getMappingClassName($source);
    }
}
