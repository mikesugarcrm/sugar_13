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
 * @ticket bug40216
 */
class Bug40216Test extends TestCase
{
    private $moduleName;

    protected function setUp(): void
    {
        $this->moduleName = 'TestModule' . random_int(0, mt_getrandmax());

        sugar_mkdir("modules/{$this->moduleName}/metadata", null, true);
        sugar_mkdir("custom/modules/{$this->moduleName}/metadata", null, true);
    }

    protected function tearDown(): void
    {
        rmdir_recursive("modules/{$this->moduleName}");
        rmdir_recursive("custom/modules/{$this->moduleName}");
    }

    public function testAdditionalDetailsMetadataFileIsFound()
    {
        sugar_touch("modules/{$this->moduleName}/metadata/additionalDetails.php");

        $viewObject = new Bug40216Mock();

        $this->assertEquals(
            "modules/{$this->moduleName}/metadata/additionalDetails.php",
            $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
        );
    }

    public function testCustomAdditionalDetailsMetadataFileIsFound()
    {
        sugar_touch("custom/modules/{$this->moduleName}/metadata/additionalDetails.php");

        $viewObject = new Bug40216Mock();

        $this->assertEquals(
            "custom/modules/{$this->moduleName}/metadata/additionalDetails.php",
            $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
        );
    }

    public function testCustomAdditionalDetailsMetadataFileIsUsedBeforeNonCustomOne()
    {
        sugar_touch("modules/{$this->moduleName}/metadata/additionalDetails.php");
        sugar_touch("custom/modules/{$this->moduleName}/metadata/additionalDetails.php");

        $viewObject = new Bug40216Mock();

        $this->assertEquals(
            "custom/modules/{$this->moduleName}/metadata/additionalDetails.php",
            $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
        );
    }
}

class Bug40216Mock extends HomeViewAdditionaldetailsretrieve
{
    public function getAdditionalDetailsMetadataFile(
        $moduleName
    ) {

        return parent::getAdditionalDetailsMetadataFile($moduleName);
    }
}
