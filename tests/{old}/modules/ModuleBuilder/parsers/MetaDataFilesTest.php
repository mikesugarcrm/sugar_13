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
 * MetaDataFilesTest
 *
 * This test checks to see that the correct files are loaded from the clients/ directories
 * @coversDefaultClass MetaDataFiles
 */
class MetaDataFilesTest extends TestCase
{
    /**
     * @var mixed[]|mixed|string[]
     */
    public $createdFiles;
    /**
     * @var mixed[]|mixed|string[]
     */
    public $createdDirs;

    protected function setUp(): void
    {
        $this->createdFiles = [];
        $this->createdDirs = [];
        SugarTestHelper::setUp('app_list_strings');
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            unlink($file);
        }
        foreach ($this->createdDirs as $dir) {
            rmdir_recursive($dir);
        }
    }

    public $fileFullPaths = [
        'Accountsmobilelistviewbase' => 'modules/Accounts/clients/mobile/views/list/list.php',
        'Accountsmobilelistviewcustom' => 'custom/modules/Accounts/clients/mobile/views/list/list.php',
        'Bugsportalrecordviewworking' => 'custom/working/modules/Bugs/clients/portal/views/record/record.php',
        'Casesportalrecordviewhistory' => 'custom/history/modules/Cases/clients/portal/views/record/record.php',
        'Bugsmobilesearchviewbase' => 'modules/Bugs/clients/mobile/views/search/search.php',
        'Callsbasesearchviewbase' => 'modules/Calls/clients/base/views/search/search.php',
    ];

    public $deployedFileNames = [
        'Accountslistviewbase' => 'modules/Accounts/metadata/listviewdefs.php',
        'Leadswirelesseditviewcustommobile' => 'custom/modules/Leads/clients/mobile/views/edit/edit.php',
        'Notesportalrecordviewworkingportal' => 'custom/working/modules/Notes/clients/portal/views/record/record.php',
        'Quotesadvanced_searchhistory' => 'custom/history/modules/Quotes/metadata/searchdefs.php',
        'Meetingsbasic_searchbase' => 'modules/Meetings/metadata/searchdefs.php',
        'Bugswireless_advanced_searchbasemobile' => 'modules/Bugs/clients/mobile/views/search/search.php',
    ];

    public $undeployedFileNames = [
        'Accountslistviewbase' => 'custom/modulebuilder/packages/LZWYZ/modules/Accounts/metadata/listviewdefs.php',
        'Leadswirelesseditviewcustommobile' => 'custom/modulebuilder/packages/LZWYZ/modules/Leads/clients/mobile/views/edit/edit.php',
        'Notesportalrecordviewworkingportal' => 'custom/modulebuilder/packages/LZWYZ/modules/Notes/clients/portal/views/record/record.php',
        'Quotesadvanced_searchhistory' => 'custom/working/modulebuilder/packages/LZWYZ/modules/Quotes/metadata/searchdefs.php',
    ];

    /**
     * @dataProvider MetaDataFileFullPathProvider
     * @param string $module
     * @param string $viewtype
     * @param string $location
     * @param string $client
     * @param string $component
     */
    public function testMetaDataFileFullPath($module, $viewtype, $location, $client, $component)
    {
        $filepath = MetaDataFiles::getModuleFileName($module, $viewtype, $location, $client, $component);
        $known = $this->fileFullPaths[$module . $client . $viewtype . $component . $location];

        $this->assertEquals($known, $filepath, 'Filepath mismatch: ' . $filepath . ' <-> ' . $known);
    }

    /**
     * @dataProvider DeployedFileNameProvider
     * @param string $view
     * @param string $module
     * @param string $location
     * @param string $client
     */
    public function testDeployedFileName($view, $module, $location, $client)
    {
        $name = MetaDataFiles::getDeployedFileName($view, $module, $location, $client);
        $known = $this->deployedFileNames[$module . $view . $location . $client];
        $this->assertEquals($known, $name, 'Filename mismatch: ' . $name . ' <-> ' . $known);
    }

    /**
     * @dataProvider UndeployedFileNameProvider
     * @param string $view
     * @param string $module
     * @param string $package
     * @param string $location
     * @param string $client
     */
    public function testUndeployedFileName($view, $module, $package, $location, $client)
    {
        $name = MetaDataFiles::getUndeployedFileName($view, $module, $package, $location, $client);
        $known = $this->undeployedFileNames[$module . $view . $location . $client];
        $this->assertEquals($known, $name, 'Filename mismatch: ' . $name . ' <-> ' . $known);
    }

    public function MetaDataFileFullPathProvider()
    {
        return [
            ['Accounts', 'list', MB_BASEMETADATALOCATION, MB_WIRELESS, 'view'],
            ['Accounts', 'list', MB_CUSTOMMETADATALOCATION, MB_WIRELESS, 'view'],
            ['Bugs', 'search', MB_BASEMETADATALOCATION, MB_WIRELESS, 'view'],
            ['Bugs', 'record', MB_WORKINGMETADATALOCATION, MB_PORTAL, 'view'],
            ['Cases', 'record', MB_HISTORYMETADATALOCATION, MB_PORTAL, 'view'],
            ['Calls', 'search', MB_BASEMETADATALOCATION, 'base', 'view'],
        ];
    }

    public function DeployedFileNameProvider()
    {
        return [
            [MB_LISTVIEW, 'Accounts', MB_BASEMETADATALOCATION, ''],
            [MB_WIRELESSEDITVIEW, 'Leads', MB_CUSTOMMETADATALOCATION, MB_WIRELESS],
            [MB_PORTALRECORDVIEW, 'Notes', MB_WORKINGMETADATALOCATION, MB_PORTAL],
            [MB_ADVANCEDSEARCH, 'Quotes', MB_HISTORYMETADATALOCATION, ''],
            [MB_BASICSEARCH, 'Meetings', MB_BASEMETADATALOCATION, ''],
            [MB_WIRELESSADVANCEDSEARCH, 'Bugs', MB_BASEMETADATALOCATION, MB_WIRELESS],
        ];
    }

    public function UndeployedFileNameProvider()
    {
        return [
            [MB_LISTVIEW, 'Accounts', 'LZWYZ', MB_BASEMETADATALOCATION, ''],
            [MB_WIRELESSEDITVIEW, 'Leads', 'LZWYZ', MB_CUSTOMMETADATALOCATION, MB_WIRELESS],
            [MB_PORTALRECORDVIEW, 'Notes', 'LZWYZ', MB_WORKINGMETADATALOCATION, MB_PORTAL],
            [MB_ADVANCEDSEARCH, 'Quotes', 'LZWYZ', MB_HISTORYMETADATALOCATION, ''],
        ];
    }

    public function testLoadingExtFiles()
    {
        //Start with base app extensions
        $baseFilePath = 'custom/clients/base/views/fo/fo.php';
        $this->createdFiles[] = $baseFilePath;
        $this->createdDirs[] = dirname($baseFilePath);
        SugarAutoLoader::ensureDir(dirname($baseFilePath));

        $baseMetaContents = '<?php' . "\n" . '$viewdefs["base"]["view"]["fo"] = array("erma"=>"base");';
        file_put_contents($baseFilePath, $baseMetaContents);


        $extFilePath = 'custom/application/Ext/clients/base/views/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $baseExtMetaContents = '<?php' . "\n" . '$viewdefs["base"]["view"]["fo"]["ext"] = "baseByExt";';
        file_put_contents($extFilePath, $baseExtMetaContents);

        $baseFileList = MetaDataFiles::getClientFiles(['base'], 'view');

        $this->assertArrayHasKey($baseFilePath, $baseFileList, "Didn't find the fo section.");
        $this->assertArrayHasKey($extFilePath, $baseFileList, "Didn't find the fo extension");

        $results = MetaDataFiles::getClientFileContents($baseFileList, 'view');

        $this->assertArrayHasKey('fo', $results, "Didn't load the fo meta.");
        $this->assertArrayHasKey('ext', $results['fo']['meta'], "Didn't load the fo meta extension correctly");
        $this->assertArrayHasKey('erma', $results['fo']['meta'], 'The metadata extension was not merged with the base meta');
    }


    public function testLoadingModuleExtFiles()
    {
        //Check module specific extensions

        $baseFilePath = 'modules/Accounts/clients/base/views/fo/fo.php';
        $this->createdFiles[] = $baseFilePath;
        $this->createdDirs[] = dirname($baseFilePath);
        SugarAutoLoader::ensureDir(dirname($baseFilePath));
        $acctMetaContents = '<?php' . "\n" . '$viewdefs["Accounts"]["base"]["view"]["fo"] = array("erma"=>"baseAcct");';
        file_put_contents($baseFilePath, $acctMetaContents);

        $extFilePath = 'custom/modules/Accounts/Ext/clients/base/views/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $acctExtMetaContents = '<?php' . "\n" . '$viewdefs["Accounts"]["base"]["view"]["fo"]["ext"] = "baseAcctByExt";';
        file_put_contents($extFilePath, $acctExtMetaContents);

        $accountFileList = MetaDataFiles::getClientFiles(['base'], 'view', 'Accounts');

        $this->assertArrayHasKey($baseFilePath, $accountFileList, "Didn't find the Accounts fo section.");
        $this->assertArrayHasKey($extFilePath, $accountFileList, "Didn't find the Accounts fo extension");

        $results = MetaDataFiles::getClientFileContents($accountFileList, 'view', 'Accounts');

        $this->assertArrayHasKey('fo', $results, "Didn't load the Accounts fo meta.");
        $this->assertArrayHasKey('ext', $results['fo']['meta'], "Didn't load the Accounts fo meta extension correctly");
        $this->assertArrayHasKey('erma', $results['fo']['meta'], 'The Accounts metadata extension was not merged with the base meta');
    }

    /**
     * Test merging the extension file to the global file.
     *
     * @return none
     */
    public function testMergeModuleExtFiles2Base()
    {
        //Load the base file
        $baseFilePath = 'clients/base/layouts/fo/fo.php';
        $this->createdFiles[] = $baseFilePath;
        $this->createdDirs[] = dirname($baseFilePath);
        SugarAutoLoader::ensureDir(dirname($baseFilePath));
        $baseMetaContents = '<?php' . "\n" . '$viewdefs["base"]["layout"]["fo"] = array("erma"=>"baseLayouts");';
        file_put_contents($baseFilePath, $baseMetaContents);

        //Load the extension file
        $extFilePath = 'custom/modules/Cases/Ext/clients/base/layouts/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $caseExtMetaContents = '<?php' . "\n" . '$viewdefs["Cases"]["base"]["layout"]["fo"]["ext"] = "baseCaseByExt";';
        file_put_contents($extFilePath, $caseExtMetaContents);

        $caseFileList = MetaDataFiles::getClientFiles(['base'], 'layout', 'Cases');
        $this->assertArrayHasKey($extFilePath, $caseFileList, "Didn't find the Cases fo extension");

        $results = MetaDataFiles::getClientFileContents($caseFileList, 'layout', 'Cases');
        $this->assertArrayHasKey('fo', $results, "Didn't load the Cases fo meta.");
        $this->assertArrayHasKey('ext', $results['fo']['meta'], "Didn't load the Cases fo meta extension correctly");
        $this->assertArrayHasKey(
            'erma',
            $results['fo']['meta'],
            'The Cases metadata extension was not merged with the base meta'
        );
    }

    /**
     * Test merging the extension file to the template file.
     *
     * @return none
     */
    public function testMergeModuleExtFiles2Template()
    {
        //Load the template file
        $templateFilePath = 'include/SugarObjects/templates/basic/clients/base/views/fo/fo.php';
        $this->createdFiles[] = $templateFilePath;
        $this->createdDirs[] = dirname($templateFilePath);
        SugarAutoLoader::ensureDir(dirname($templateFilePath));
        $baseMetaContents = '<?php' . "\n" . '$module_name = "<module_name>";' . "\n" .
            '$viewdefs[$module_name]["base"]["view"]["fo"] = array("erma"=>"baseViews");';
        file_put_contents($templateFilePath, $baseMetaContents);

        //Load the extension file
        $extFilePath = 'custom/modules/Cases/Ext/clients/base/views/fo/fo.ext.php';
        $this->createdFiles[] = $extFilePath;
        $this->createdDirs[] = dirname($extFilePath);
        SugarAutoLoader::ensureDir(dirname($extFilePath));
        $caseExtMetaContents = '<?php' . "\n" . '$viewdefs["Cases"]["base"]["view"]["fo"]["ext"] = "baseCaseByExt";';
        file_put_contents($extFilePath, $caseExtMetaContents);

        $caseFileList = MetaDataFiles::getClientFiles(['base'], 'view', 'Cases');
        $this->assertArrayHasKey($templateFilePath, $caseFileList, "Didn't find the template fo section.");
        $this->assertArrayHasKey($extFilePath, $caseFileList, "Didn't find the Cases fo extension");

        $results = MetaDataFiles::getClientFileContents($caseFileList, 'view', 'Cases');
        $this->assertArrayHasKey('fo', $results, "Didn't load the Cases fo meta.");
        $this->assertArrayHasKey('ext', $results['fo']['meta'], "Didn't load the Cases fo meta extension correctly");
        $this->assertArrayHasKey(
            'erma',
            $results['fo']['meta'],
            'The Cases metadata extension was not merged with the base meta'
        );
    }

    /**
     * @dataProvider providerTestGetDropdownViewFileAttributes
     * @covers ::getDropdownViewFileAttributes
     * @param string $path the file path to test
     * @param array|false $expected the expected dropdown file attributes
     */
    public function testGetDropdownViewFileAttributes($path, $expected)
    {
        $this->assertEquals($expected, MetaDataFiles::getDropdownViewFileAttributes($path));
    }

    /**
     * @return array test data for testGetDropdownViewFileAttributes
     */
    public function providerTestGetDropdownViewFileAttributes()
    {
        return [
            [
                'modules/Accounts/clients/base/views/record/dropdowns/account_type/Customer/record.php',
                [
                    'view' => 'record',
                    'dropdownField' => 'account_type',
                    'dropdownValue' => 'Customer',
                ],
            ],
            [
                'modules/Accounts/clients/base/views/record/record.php',
                false,
            ],
        ];
    }

    /**
     * @covers ::getClientFileList
     */
    public function testGetClientFileListFiltersByDropdown()
    {
        $dropdownFilePath = 'custom/modules/Accounts/clients/base/views/record/dropdowns/fake_field/FakeValue/record.php';
        $this->createdFiles[] = $dropdownFilePath;
        $this->createdDirs[] = 'custom/modules/Accounts/clients/base/views/record/dropdowns/fake_field';
        SugarAutoLoader::ensureDir(dirname($dropdownFilePath));
        $dropdownMeta = '<?php' . "\n" . '$viewdefs["Accounts"]["base"]["view"]["record"] = ["components" => []];';
        sugar_file_put_contents($dropdownFilePath, $dropdownMeta);

        // Do not include dropdown view files in regular view fetches
        $regularViews = MetaDataFiles::getClientFiles(['base'], 'view', 'Accounts');
        $this->assertArrayHasKey('modules/Accounts/clients/base/views/record/record.php', $regularViews);
        $this->assertArrayNotHasKey($dropdownFilePath, $regularViews);

        // Do not include regular view files in dropdown view fetches
        $dropdownViews = MetaDataFiles::getClientFiles(['base'], 'dropdownViews', 'Accounts');
        $this->assertArrayHasKey($dropdownFilePath, $dropdownViews);
        $this->assertArrayNotHasKey('modules/Accounts/clients/base/views/record/record.php', $dropdownViews);
    }

    /**
     * @covers ::getClientFileContents
     */
    public function testGetClientFileContentsWithDropdownMeta()
    {
        $dropdownFilePath = 'custom/modules/Accounts/clients/base/views/record/dropdowns/fake_field/FakeValue/record.php';
        $this->createdFiles[] = $dropdownFilePath;
        $this->createdDirs[] = 'custom/modules/Accounts/clients/base/views/record/dropdowns/fake_field';
        SugarAutoLoader::ensureDir(dirname($dropdownFilePath));
        $dropdownMeta = '<?php' . "\n" . '$viewdefs["Accounts"]["base"]["view"]["record"] = ["components" => []];';
        sugar_file_put_contents($dropdownFilePath, $dropdownMeta);

        $dropdownViews = MetaDataFiles::getClientFiles(['base'], 'dropdownViews', 'Accounts');
        $dropdownContents = MetaDataFiles::getClientFileContents($dropdownViews, 'dropdownViews', 'Accounts');
        $this->assertNotEmpty($dropdownContents['record']['fake_field']['FakeValue']['meta']);
    }
}
