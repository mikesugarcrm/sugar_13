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

require_once 'upgrade/scripts/post/7_FixSingularList.php';

use PHPUnit\Framework\MockObject\MockObject;

class UpgradeSingularListTest extends UpgradeTestCase
{
    protected $testModule = 'test_singleTest';

    /**
     * Rebuild languages
     */
    protected function rebuildLang()
    {
        $mi = new ModuleInstaller();
        $mi->silent = true;
        $mi->rebuild_languages(['en_us' => 'en_us']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        mkdir_recursive('custom/Extension/application/Ext/Language/');
        file_put_contents(
            'custom/Extension/application/Ext/Language/en_us.test.php',
            '<?php $app_list_strings["moduleList"]["' . $this->testModule . '"] = "singtest";'
        );

        $this->rebuildLang();
        SugarTestHelper::saveFile($this->getPackageLangFile());
        SugarTestHelper::saveFile($this->getLangPath());

        $this->upgrader->state['MBModules'] = [$this->testModule];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        SugarTestHelper::tearDown();

        if (file_exists('custom/Extension/application/Ext/Language/en_us.test.php')) {
            unlink('custom/Extension/application/Ext/Language/en_us.test.php');
        }

        $this->rebuildLang();
    }

    /**
     * Test for ScanModules
     */
    public function testFixSingular()
    {
        $script = $this->getMockScript();
        $script->run();

        $app_list_strings = [];

        $this->assertFileExists($this->getPackageLangFile());
        include $this->getPackageLangFile();

        $this->assertEquals('singtest', $app_list_strings['moduleListSingular'][$this->testModule]);
    }

    /**
     * Test upgrade script fix both "moduleList" and "moduleListSingular" entries
     */
    public function testFixModuleListStrings()
    {
        // unlink file with moduleList translations
        unlink('custom/Extension/application/Ext/Language/en_us.test.php');
        $this->rebuildLang();

        // Create module language file
        $content = '<?php $mod_strings = array("LBL_MODULE_NAME" => "SingleTest");';
        file_put_contents($this->getLangPath(), $content);

        $script = $this->getMockScript();
        $script->run();

        $app_list_strings = [];
        $this->assertFileExists($this->getPackageLangFile());
        include $this->getPackageLangFile();

        $this->assertEquals('SingleTest', $app_list_strings['moduleList'][$this->testModule]);
        $this->assertEquals('SingleTest', $app_list_strings['moduleListSingular'][$this->testModule]);
    }

    /**
     * Mock post upgrade script to return custom language file
     *
     * @return MockObject
     */
    protected function getMockScript()
    {
        $mock = $this->getMockBuilder('SugarUpgradeFixSingularList')
            ->disableOriginalConstructor()
            ->setMethods(['getLanguageFilePath', 'getPackages', 'getPackageLangFile'])
            ->getMock();

        $mock->upgrader = $this->upgrader;

        $keys = explode('_', $this->testModule);
        $packages = [$keys[0] => $keys[0]];

        $mock->expects($this->any())
            ->method('getPackages')
            ->will($this->returnValue($packages));

        $mock->expects($this->any())
            ->method('getPackageLangFile')
            ->will($this->returnValue($this->getPackageLangFile()));

        $mock->expects($this->any())
            ->method('getLanguageFilePath')
            ->will($this->returnValue($this->getLangPath()));

        return $mock;
    }

    /**
     * Get fake package lang file
     *
     * @return string
     */
    protected function getPackageLangFile()
    {
        return 'cache/test.en_us.lang.php';
    }

    /**
     * Get fake lang file path
     *
     * @return string
     */
    protected function getLangPath()
    {
        return 'cache/' . $this->testModule . '.en_us.lang.php';
    }
}
