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

class ParserDropdownTest extends TestCase
{
    /**
     * @var string|bool|mixed
     */
    public $fileBackup;
    /**
     * @var string|bool|mixed
     */
    public $modListBackup;
    // Custom include/language file path
    private $customFile;

    // Custom modlist file path
    private $customModList;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->customFile = 'custom/include/language/' . $GLOBALS['current_language'] . '.lang.php';
        if (file_exists($this->customFile)) {
            $this->fileBackup = file_get_contents($this->customFile);
        }

        $this->customModList = 'custom/Extension/application/Ext/Language/' . $GLOBALS['current_language'] . '.sugar_moduleList.php';
        if (file_exists($this->customModList)) {
            $this->modListBackup = file_get_contents($this->customModList);
            unlink($this->customModList);
        }

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
    }

    protected function tearDown(): void
    {
        if (isset($this->fileBackup)) {
            file_put_contents($this->customFile, $this->fileBackup);
        } elseif (file_exists($this->customFile)) {
            unlink($this->customFile);
        }

        if (isset($this->modListBackup)) {
            file_put_contents($this->customModList, $this->modListBackup);
        }

        // Clear cache so it can be reloaded later
        $cache_key = 'app_list_strings.' . $GLOBALS['current_language'];
        sugar_cache_clear($cache_key);

        // Reload app_list_strings
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $_REQUEST = [];

        SugarTestHelper::tearDown();
    }

    public function testSavingEmptyLabels()
    {
        $_REQUEST['view_package'] = 'studio';
        $params = [
            'dropdown_name' => 'moduleList',
            'list_value' => '',
            'sales_stage_classification' => '',
            'skipSaveExemptDropdowns' => true,
        ];

        $parser = $this->createPartialMock('ParserDropDown', ['saveExemptDropdowns', 'synchDropDown', 'saveContents', 'finalize']);
        $parser->expects($this->never())->method('saveExemptDropdowns');
        $parser->saveDropDown($params);
    }

    /**
     * Check if saveExemptDropdowns works as expected
     * This method should set a NULL value to keys that were deleted on exempt dropdowns
     *
     * @param $dropdownValues New dropdown values
     * @param $dropdownName Dropdown key
     * @param $appListStrings Old app_list_strings, containing old dropdown values
     * @param $customFileContents Contents of custom/include/language file
     * @param $expected Dropdown returned from saveExemptDropdowns call
     *
     * @dataProvider providerTestSaveExemptDropdowns
     */
    public function testSaveExemptDropdowns(
        $dropdownValues,
        $dropdownName,
        $appListStrings,
        $customFileContents,
        $expected
    ) {

        $dirName = dirname($this->customFile);
        SugarAutoLoader::ensureDir($dirName);
        file_put_contents($this->customFile, $customFileContents);

        $parser = new ParserDropDown();
        $output = $parser->saveExemptDropdowns(
            $dropdownValues,
            $dropdownName,
            $appListStrings,
            $GLOBALS['current_language']
        );

        $this->assertEquals($expected, $output, 'Save Exempt Dropdowns not working properly.');
    }

    public static function providerTestSaveExemptDropdowns()
    {
        return [
            // Check if non-exempt dropdowns are just passed through
            [
                [
                    0 => 'test 0',
                ],
                'test',
                [
                    'test' => [
                        0 => 'test 0',
                        1 => 'test 1',
                    ],
                ],
                '',
                [
                    0 => 'test 0',
                ],
            ],
            // Check if deleted exempt dropdown values are NULL
            [
                [
                    0 => 'test 0',
                ],
                'parent_type_display',
                [
                    'parent_type_display' => [
                        0 => 'test 0',
                        1 => 'test 1',
                    ],
                ],
                '',
                [
                    0 => 'test 0',
                    1 => null,
                ],
            ],
            // Check if NULL values from custom/include/language file are copied over so we don't loose the keys
            [
                [
                    0 => 'test 0',
                ],
                'parent_type_display',
                [
                    'parent_type_display' => [
                        0 => 'test 0',
                        1 => 'test 1',
                        2 => 'test 2',
                    ],
                ],
                "<?php
                    \$app_list_strings['parent_type_display'] = array(
                        'ProjectTask' => 'Project Task',
                        'Prospects' => null,
                    );
                ",
                [
                    0 => 'test 0',
                    1 => null,
                    2 => null,
                    'Prospects' => null,
                ],
            ],
        ];
    }

    /**
     * Check if updateSalesStageClassifications works as expected
     * This method should return an array of 2 arrays with the correct Closed Won/Lost sales stages.
     *
     * @param $dropdownValues New dropdown values
     * @param $expected Dropdown returned from updateSalesStageClassifications call
     *
     * @dataProvider updateSalesStageClassificationsProvider
     */
    public function testUpdateSalesStageClassifications(
        $dropdownValues,
        $expected
    ) {

        $parser = new ParserDropDown();
        $output = $parser->updateSalesStageClassifications($dropdownValues);

        $this->assertEquals(
            $expected,
            $output,
            'Save Updated Sales Stages are not working properly.'
        );
    }

    public static function updateSalesStageClassificationsProvider()
    {
        return [
            // Check that it's default Closed Won/Closed Lost values
            [
                static::encodeList([
                    'Prospecting' => 'Open',
                    'Qualification' => 'Open',
                    'Needs Analysis' => 'Open',
                    'Value Proposition' => 'Open',
                    'Id. Decision Makers' => 'Open',
                    'Perception Analysis' => 'Open',
                    'Proposal/Price Quote' => 'Open',
                    'Negotiation/Review' => 'Open',
                    'Closed Won' => 'Closed Won',
                    'Closed Lost' => 'Closed Lost',
                ]),
                [
                    'new_closed_won_sales_stages' => ['Closed Won'],
                    'new_closed_lost_sales_stages' => ['Closed Lost'],
                ],
            ],
            // Check if Prospecting and Value Proposition are classified properly
            [
                static::encodeList([
                    'Prospecting' => 'Closed Lost',
                    'Qualification' => 'Open',
                    'Needs Analysis' => 'Open',
                    'Value Proposition' => 'Closed Won',
                    'Id. Decision Makers' => 'Open',
                    'Perception Analysis' => 'Open',
                    'Proposal/Price Quote' => 'Open',
                    'Negotiation/Review' => 'Open',
                    'Closed Won' => 'Closed Won',
                    'Closed Lost' => 'Closed Lost',
                ]),
                [
                    'new_closed_won_sales_stages' => ['Value Proposition', 'Closed Won',],
                    'new_closed_lost_sales_stages' => ['Prospecting', 'Closed Lost',],
                ],
            ],
        ];
    }

    /**
     * @param $params
     * @param $existingFileContents
     * @param $expectedFileContents
     *
     * @dataProvider saveDropdownProvider
     */
    public function testSaveDropdown(
        $params,
        $existingFileContents,
        $expected
    ) {

        $lang = $GLOBALS['current_language'];
        $params['dropdown_lang'] = $lang;
        $dropdownName = $params['dropdown_name'];
        $this->customFile = "custom/Extension/application/Ext/Language/$lang.sugar_$dropdownName.php";
        if (!empty($existingFileContents)) {
            sugar_file_put_contents($this->customFile, $existingFileContents);
        }

        $_REQUEST['view_package'] = $params['view_package'];

        $parser = $this->getMockBuilder('ParserDropDown')
            ->disableOriginalConstructor()
            ->setMethods(['finalize'])
            ->getMock();

        $parser->saveDropDown($params);

        if (!empty($expected)) {
            $this->assertFileExists($this->customFile);

            $app_list_strings = [];
            include $this->customFile;

            $this->assertSame($expected, $app_list_strings, 'Save Dropdown not working properly.');
        } else {
            $this->assertFileDoesNotExist($this->customFile);
        }
    }


    public static function saveDropdownProvider()
    {
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        return [
            //Add a new module with no existing customization
            [
                //Params
                [
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList(array_merge(
                        $app_list_strings['moduleList'],
                        ['NewModule' => 'New Module']
                    )),
                    'sales_stage_classification' => '',
                    'view_package' => 'studio',
                    'use_push' => true,
                ],
                '',
                ['moduleList' => ['NewModule' => 'New Module']],
            ],
            //Rename existing module
            [
                //Params
                [
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList(array_merge(
                        $app_list_strings['moduleList'],
                        ['Accounts' => 'New Accounts']
                    )),
                    'sales_stage_classification' => '',
                    'view_package' => 'studio',
                    'use_push' => true,
                ],
                '',
                ['moduleList' => ['Accounts' => 'New Accounts']],
            ],
            //No change
            [
                //Params
                [
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList($app_list_strings['moduleList']),
                    'sales_stage_classification' => '',
                    'view_package' => 'studio',
                    'use_push' => true,
                ],
                '',
                false,
            ],
            //Keep existing customization
            [
                //Params
                [
                    'dropdown_name' => 'moduleList',
                    'list_value' => static::encodeList(array_merge(
                        $app_list_strings['moduleList'],
                        ['NewModule' => 'New Module']
                    )),
                    'sales_stage_customization' => '',
                    'view_package' => 'studio',
                    'use_push' => true,
                ],
                '<?php $app_list_strings[\'moduleList\'][\'foo\']=\'bar\';',
                ['moduleList' => ['foo' => 'bar', 'NewModule' => 'New Module']],
            ],
        ];
    }

    protected static function encodeList(array $list)
    {
        $new_list = [];
        foreach ($list as $k => $v) {
            $new_list[] = [$k, $v];
        }

        return json_encode($new_list);
    }
}
