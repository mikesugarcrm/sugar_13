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

use PHPUnit\Framework\MockObject\MockObject;

class UpgradeRenameModuleTest extends UpgradeTestCase
{
    protected $globalFilename = 'custom/include/language/en_us.lang.php';
    protected $moduleFilename = 'custom/modules/Contacts/Ext/Language/en_us.lang.ext.php';
    protected $moduleExtLang = 'custom/Extension/modules/Contacts/Ext/Language/en_us.lang.php';
    protected $alsBackup;
    protected $modBackup;
    protected $changedModuleList = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists($this->globalFilename)) {
            copy($this->globalFilename, $this->globalFilename . '.bak');
        }
        if (file_exists($this->moduleFilename)) {
            copy($this->moduleFilename, $this->moduleFilename . '.bak');
        }

        LanguageManager::clearLanguageCache('Contacts');

        sugar_mkdir(dirname($this->globalFilename), null, true);

        $GLOBALS['current_language'] = 'en_us';
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['Contacts']);

        $this->alsBackup = $GLOBALS['app_list_strings'];
        $this->modBackup = $GLOBALS['mod_strings'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->globalFilename . '.bak')) {
            copy($this->globalFilename . '.bak', $this->globalFilename);
        }
        if (file_exists($this->moduleFilename . '.bak')) {
            copy($this->moduleFilename . '.bak', $this->moduleFilename);
        }

        foreach ($this->changedModuleList as $row) {
            if (file_exists('custom/modules/' . $row . '/Ext/Language/' . $GLOBALS['current_language'] . '.lang.ext.php')) {
                unlink('custom/modules/' . $row . '/Ext/Language/' . $GLOBALS['current_language'] . '.lang.ext.php');
            }

            if (file_exists('custom/Extension/modules/' . $row . '/Ext/Language/' . $GLOBALS['current_language'] . '.lang.php')) {
                unlink('custom/Extension/modules/' . $row . '/Ext/Language/' . $GLOBALS['current_language'] . '.lang.php');
            }
        }

        $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];
        $GLOBALS['app_list_strings'] = $this->alsBackup;
        $GLOBALS['mod_strings'] = $this->modBackup;
    }

    protected function handleChangedModuleList($list)
    {
        foreach ($list as $key => $value) {
            if ($key == $GLOBALS['current_language']) {
                foreach ($value as $module => $status) {
                    if (!in_array($module, $this->changedModuleList)) {
                        $this->changedModuleList[] = $module;
                    }
                }
            }
        }
    }

    public function testUpgradeRename()
    {
        $app_list_strings = [];
        $toWrite = "<?php
\$app_list_strings['moduleListSingular']['Contacts']='Property Contact';
\$app_list_strings['moduleList']['Contacts']='Property Contacts';";
        sugar_file_put_contents($this->globalFilename, $toWrite);
        $GLOBALS['app_list_strings']['moduleListSingular']['Contacts'] = 'Property Contact';
        $GLOBALS['app_list_strings']['moduleList']['Contacts'] = 'Property Contacts';

        $this->upgrader->setVersions('6.7.3', 'ent', '7.1.5', 'ent');
        $script = $this->upgrader->getScript('post', '7_RenameModules');

        $changedModuleList = $script->run();
        $this->handleChangedModuleList($changedModuleList);

        /*
         * Ensure that even on the second run it still stays as "Property Contacts"
         * instead of "Property Property Contacts"
         */
        $changedModuleList = $script->run();
        $this->handleChangedModuleList($changedModuleList);

        include $this->globalFilename;
        $this->assertEquals($app_list_strings['moduleListSingular']['Contacts'], 'Property Contact');
        $this->assertEquals($app_list_strings['moduleList']['Contacts'], 'Property Contacts');
    }

    public function testUpgradeRenameWithIntendedDouble()
    {
        $app_list_strings = [];
        $toWrite = "<?php
\$app_list_strings['moduleListSingular']['Contacts']='New Contact';
\$app_list_strings['moduleList']['Contacts']='New Contacts';";
        sugar_file_put_contents($this->globalFilename, $toWrite);
        $GLOBALS['app_list_strings']['moduleListSingular']['Contacts'] = 'New Contact';
        $GLOBALS['app_list_strings']['moduleList']['Contacts'] = 'New Contacts';

        $this->upgrader->setVersions('6.7.3', 'ent', '7.1.5', 'ent');
        $script = $this->upgrader->getScript('post', '7_RenameModules');

        $changedModuleList = $script->run();
        $this->handleChangedModuleList($changedModuleList);

        include $this->globalFilename;
        $mod_strings = return_module_language('en_us', 'Contacts', true);
        $this->assertEquals($app_list_strings['moduleListSingular']['Contacts'], 'New Contact');
        $this->assertEquals($app_list_strings['moduleList']['Contacts'], 'New Contacts');
        $this->assertEquals($mod_strings['LBL_NEW_FORM_TITLE'], 'New New Contact');
    }

    /**
     * If module in some non us language has untranslated labels which are not changed after upgrade
     * then we should not process them through RenameModules object
     *
     * Problem was that moduleList uses english label is language does not have it.
     * Because of that process of renaming of labels is executed but should not.
     *
     * @dataProvider getAppListStringsSets
     *
     * @param array $appListStringLang
     * @param array $appListStringLangCore
     * @param array $appListStringDefault
     */
    public function testNonUsLangWithUntranslatedModuleNameShouldNotBeProcessed(array $appListStringLang, array $appListStringLangCore, array $appListStringDefault)
    {
        /** @var MockObject|RenameModules $renameModules */
        $renameModules = $this->createMock('RenameModules');
        $renameModules->expects($this->never())->method('getModuleSingularKey');
        $renameModules->expects($this->never())->method('changeModuleModStrings');
        $renameModules->expects($this->never())->method('setChangedModules');
        $renameModules->expects($this->never())->method('changeStringsInRelatedModules');
        $renameModules->expects($this->never())->method('getRenamedModules');

        /** @var MockObject|SugarUpgradeRenameModules $script */
        $script = $this->getMockBuilder('SugarUpgradeRenameModules')
            ->setMethods([
                'getLanguages',
                'getDefaultAppListStrings',
                'getAppListStrings',
                'getCoreAppListStrings',
                'getRenameModulesInstance',
            ])
            ->setConstructorArgs([$this->getMockForAbstractClass('UpgradeDriver')])
            ->getMock();
        $script->expects($this->once())->method('getLanguages')->will($this->returnValue(['lang_LANG' => 'Language']));
        $script->expects($this->once())->method('getDefaultAppListStrings')->will($this->returnValue($appListStringDefault));
        $script->expects($this->atLeastOnce())->method('getAppListStrings')->with($this->equalTo('lang_LANG'))->will($this->returnValue($appListStringLang));
        $script->expects($this->atLeastOnce())->method('getCoreAppListStrings')->with($this->equalTo('lang_LANG'))->will($this->returnValue($appListStringLangCore));
        $script->expects($this->once())->method('getRenameModulesInstance')->will($this->returnValue($renameModules));

        $actual = $script->run();
        $this->assertArrayHasKey('lang_LANG', $actual, 'Language was not checked');
        $this->assertEmpty($actual['lang_LANG'], 'Language should not have changed labels');
    }

    /**
     * Data provider
     *
     * @return array
     * @see UpgradeRenameModuleTest::testNonUsLangWithUntranslatedModuleNameShouldNotBeProcessed
     *
     */
    public static function getAppListStringsSets()
    {
        return [
            'moduleListIsNotTranslatedInNonUsLanguage' => [
                [
                    'moduleListSingular' => [
                        'test' => 'Translated Singular String',
                    ],
                    'moduleList' => [
                        'test' => 'English Plural String', // means it wasn't translated and loaded from english lang
                    ],
                ],
                [
                    'moduleListSingular' => [
                        'test' => 'Translated Singular String',
                    ],
                    'moduleList' => [
                        'test' => '', // we don't have translation for module
                    ],
                ],
                [
                    'moduleListSingular' => [
                        'test' => 'English Singular String',
                    ],
                    'moduleList' => [
                        'test' => 'English Plural String',
                    ],
                ],

            ],
        ];
    }
}
