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

class MBLanguageTest extends TestCase
{
    private $testPath = 'cache' . DIRECTORY_SEPARATOR . 'MBTest';
    private $testDest = 'cache' . DIRECTORY_SEPARATOR . 'MBTestDest';

    protected function setUp(): void
    {
        rmdir_recursive($this->testDest);
        sugar_mkdir($this->testPath . DIRECTORY_SEPARATOR . 'language', null, true);
        sugar_touch($this->testPath . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'test.php');
        sugar_mkdir($this->testDest, null, true);
    }

    protected function tearDown(): void
    {
        rmdir_recursive($this->testPath);
        rmdir_recursive($this->testDest);
        SugarTestHelper::tearDown();
    }

    public function testBuild()
    {
        $mbLang = new MBLanguage(
            'modName',
            $this->testPath,
            'modNames',
            'keyName_modName',
            'modName'
        );
        $mbLang->build($this->testDest);
        $this->assertDirectoryExists(realpath($this->testDest));
        $this->assertFileExists(realpath($this->testPath . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'test.php'));
    }
}
