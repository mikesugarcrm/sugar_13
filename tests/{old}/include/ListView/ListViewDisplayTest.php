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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Validator\Validator;

class ListViewDisplayTest extends TestCase
{
    private $save_query;

    /**
     * @var ListViewDisplay
     */
    private $lvd;

    protected function setUp(): void
    {
        SugarTestHelper::setup('moduleList');
        $GLOBALS['moduleList'][] = 'foo';
        Validator::clearValidatorsCache();

        $this->lvd = new ListViewDisplay();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        global $sugar_config;
        if (isset($sugar_config['save_query'])) {
            $this->save_query = $sugar_config['save_query'];
        }
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        if (!empty($this->save_query)) {
            $sugar_config['save_query'] = $this->save_query;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);

        SugarTestHelper::tearDown();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('ListViewData', $this->lvd->lvd);
        $this->assertIsArray($this->lvd->searchColumns);
    }

    public function testShouldProcessWhenConfigSaveQueryIsNotSet()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = null;

        $this->assertTrue($this->lvd->shouldProcess('foo'));
        $this->assertTrue($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenConfigSaveQueryIsNotPopulateOnly()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_always';

        $this->assertTrue($this->lvd->shouldProcess('foo'));
        $this->assertTrue($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsTrue()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = true;

        $this->assertTrue($this->lvd->shouldProcess('foo'));
        $this->assertTrue($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsTrue()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = true;
        $_REQUEST['module'] = 'foo';

        $this->assertFalse($this->lvd->shouldProcess('foo'));
        $this->assertFalse($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoNotEqual()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'bar';

        $this->assertTrue($this->lvd->shouldProcess('foo'));
        $this->assertTrue($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndQueryIsEmpty()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = '';
        $_SESSION['last_search_mod'] = '';

        $this->assertFalse($this->lvd->shouldProcess('foo'));
        $this->assertFalse($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndQueryEqualsMsi()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = 'MSI';
        $_SESSION['last_search_mod'] = '';

        $this->assertFalse($this->lvd->shouldProcess('foo'));
        $this->assertFalse($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoNotEqualAndQueryDoesNotEqualsMsi()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = 'xMSI';
        $_SESSION['last_search_mod'] = '';

        $this->assertTrue($this->lvd->shouldProcess('foo'));
        $this->assertTrue($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndLastSearchModEqualsModule()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = '';
        $_SESSION['last_search_mod'] = 'foo';

        //C.L. Because of fix to 40186, the following two tests are now set to assertFalse
        $this->assertFalse($this->lvd->shouldProcess('foo'), 'Assert that ListViewDisplay->shouldProcess is false even if module is the same because no query was specified');
        $this->assertFalse($this->lvd->should_process, 'Assert that ListViewDisplay->shouldProcess class variable is false');

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testShouldProcessWhenGlobalDisplayListViewIsFalseAndRequestClearQueryIsFalseAndModulesDoEqualAndLastSearchModDoesNotEqualsModule()
    {
        if (isset($GLOBALS['sugar_config']['save_query'])) {
            $oldsavequery = $GLOBALS['sugar_config']['save_query'];
        }
        $GLOBALS['sugar_config']['save_query'] = 'populate_only';
        $GLOBALS['displayListView'] = false;
        $_REQUEST['clear_query'] = false;
        $_REQUEST['module'] = 'foo';
        $_REQUEST['query'] = '';
        $_SESSION['last_search_mod'] = 'bar';

        $this->assertFalse($this->lvd->shouldProcess('foo'));
        $this->assertFalse($this->lvd->should_process);

        if (isset($oldsavequery)) {
            $GLOBALS['sugar_config']['save_query'] = $oldsavequery;
        }
    }

    public function testProcess()
    {
        $data = [
            'data' => [1, 2, 3],
            'pageData' => ['bean' => ['moduleDir' => 'testmoduledir']],
        ];
        $this->lvd->process('foo', $data, 'testmetestme');

        $this->assertEquals(3, $this->lvd->rowCount);
        $this->assertEquals('testmoduledir2_TESTMETESTME_offset', $this->lvd->moduleString);
    }

    public function testDisplayIfShouldNotProcess()
    {
        $this->lvd->should_process = false;

        $this->assertEmpty($this->lvd->display());
    }

    public function testDisplayIfMultiSelectFalse()
    {
        $this->lvd->should_process = true;
        $this->lvd->multiSelect = false;

        $this->assertEmpty($this->lvd->display());
    }

    public function testDisplayIfShowMassUpdateFormFalse()
    {
        $this->lvd->should_process = true;
        $this->lvd->show_mass_update_form = false;

        $this->assertEmpty($this->lvd->display());
    }

    public function testDisplayIfShowMassUpdateFormTrueAndMultiSelectTrue()
    {
        $this->lvd->should_process = true;
        $this->lvd->show_mass_update_form = true;
        $this->lvd->multiSelect = true;
        $this->lvd->multi_select_popup = true;
        $this->lvd->mass = $this->createMock('MassUpdate');
        $this->lvd->mass->expects($this->any())
            ->method('getDisplayMassUpdateForm')
            ->will($this->returnValue('foo'));
        $this->lvd->mass->expects($this->any())
            ->method('getMassUpdateFormHeader')
            ->will($this->returnValue('bar'));

        $this->assertEquals('foobar', $this->lvd->display());
    }

    public function testBuildSelectLink()
    {
        $output = $this->lvd->buildSelectLink();
        $output = implode('', $output['buttons']);
        $this->assertStringContainsString("<a id='select_link'", $output);
        $this->assertStringContainsString(
            'sListView.check_all(document.MassUpdate, "mass[]", true, 0)',
            $output
        );
        $this->assertStringContainsString(
            'sListView.check_entire_list(document.MassUpdate, "mass[]",true,0);',
            $output
        );
    }

    public function testBuildSelectLinkWithParameters()
    {
        $output = $this->lvd->buildSelectLink('testtest', 1, 2);
        $output = implode('', $output['buttons']);
        $this->assertStringContainsString("<a id='testtest'", $output);
        $this->assertStringContainsString(
            'sListView.check_all(document.MassUpdate, "mass[]", true, 2)',
            $output
        );
        $this->assertStringContainsString(
            'sListView.check_entire_list(document.MassUpdate, "mass[]",true,1);',
            $output
        );
    }

    public function testBuildSelectLinkWithPageTotalLessThanZero()
    {
        $output = $this->lvd->buildSelectLink('testtest', 1, -1);
        $output = implode('', $output['buttons']);
        $this->assertStringContainsString("<a id='testtest'", $output);
        $this->assertStringContainsString(
            'sListView.check_all(document.MassUpdate, "mass[]", true, 1)',
            $output
        );
        $this->assertStringContainsString(
            'sListView.check_entire_list(document.MassUpdate, "mass[]",true,1);',
            $output
        );
    }

    public function testBuildExportLink()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->module_dir = 'testtest';
        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildExportLink');

        $this->assertStringContainsString(
            "return sListView.send_form(true, 'testtest', 'index.php?entryPoint=export',",
            $output
        );
    }

    public function testBuildMassUpdateLink()
    {
        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildMassUpdateLink');

        $this->assertMatchesRegularExpression("/.*document\.getElementById\(['\"]massupdate_form['\"]\)\.style\.display\s*=\s*['\"]['\"].*/", $output);
    }

    public function composeEmailEmptyDataProvider()
    {
        return [
            [false],
            [[]],
            [
                [
                    'field1' => [
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider composeEmailEmptyDataProvider
     */
    public function testComposeEmailEmpty($fieldDefs)
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->field_defs = $fieldDefs;

        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->lvd, 'buildComposeEmailLink', [0]));
    }

    public function testComposeEmailIfFieldDefsIfUsingSugarEmailClient()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->object_name = 'foobar';
        $this->lvd->seed->module_dir = 'foobarfoobar';
        $this->lvd->seed->field_defs = [
            'field1' => [
                'type' => 'link',
                'relationship' => 'foobar_emailaddresses',
            ],
        ];
        $GLOBALS['dictionary']['foobar']['relationships']['foobar_emailaddresses']['rhs_module'] = 'EmailAddresses';
        $GLOBALS['current_user']->setPreference('email_link_type', 'sugar');

        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildComposeEmailLink', [5]);

        $this->assertStringContainsString(", 'foobarfoobar', '5', ", $output);

        unset($GLOBALS['dictionary']['foobar']);
    }

    public function testComposeEmailIfFieldDefsIfUsingExternalEmailClient()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->object_name = 'foobar';
        $this->lvd->seed->module_dir = 'foobarfoobar';
        $_REQUEST['module'] = 'foobarfoobar';
        $this->lvd->seed->field_defs = [
            'field1' => [
                'type' => 'link',
                'relationship' => 'foobar_emailaddresses',
            ],
        ];
        $_REQUEST['module'] = 'foo';

        $GLOBALS['dictionary']['foobar']['relationships']['foobar_emailaddresses']['rhs_module'] = 'EmailAddresses';
        $GLOBALS['current_user']->setPreference('email_link_type', 'mailto');

        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildComposeEmailLink', [5]);

        $this->assertStringContainsString('sListView.use_external_mail_client', $output);

        unset($GLOBALS['dictionary']['foobar']);
        unset($_REQUEST['module']);
    }

    public function testBuildDeleteLink()
    {
        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildDeleteLink');

        $this->assertStringContainsString('return sListView.send_mass_update', $output);
    }

    public function testBuildSelectedObjectsSpan()
    {
        $output = $this->lvd->buildSelectedObjectsSpan(1, 1);

        $this->assertStringContainsString(
            "<input  style='border: 0px; background: transparent; font-size: inherit; color: inherit' type='text' id='selectCountTop' readonly name='selectCount[]' value='1' />",
            $output
        );
    }

    public function testBuildMergeDuplicatesLinkWhenModuleDoesNotHaveItEnabled()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->object_name = 'foobar';
        $this->lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['dictionary']['foobar']['duplicate_merge'] = false;
        $GLOBALS['current_user']->is_admin = 1;

        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->lvd, 'buildMergeDuplicatesLink'));
    }

    public function testBuildMergeDuplicatesLink()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->object_name = 'foobar';
        $this->lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['dictionary']['foobar']['duplicate_merge'] = true;
        $GLOBALS['current_user']->is_admin = 1;

        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildMergeDuplicatesLink');

        $this->assertStringContainsString('"foobarfoobar", "");}', htmlspecialchars_decode($output, ENT_COMPAT));
    }

    public function testBuildMergeDuplicatesLinkBuildsReturnString()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->object_name = 'foobar';
        $this->lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['dictionary']['foobar']['duplicate_merge'] = true;
        $GLOBALS['current_user']->is_admin = 1;

        $request = InputValidation::create([
            'module' => 'Accounts',
            'action' => 'bar',
            'record' => '1',
        ], []);
        SugarTestReflection::setProtectedValue($this->lvd, 'request', $request);

        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildMergeDuplicatesLink');

        $this->assertStringContainsString(
            '"foobarfoobar", "&return_module=Accounts&return_action=bar&return_id=1");}',
            htmlspecialchars_decode($output, ENT_COMPAT)
        );
    }

    public function testBuildMergeLinkWhenUserDisabledMailMerge()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->module_dir = 'foobarfoobar';
        $GLOBALS['current_user']->setPreference('mailmerge_on', 'off');

        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->lvd, 'buildMergeLink'));
    }

    public function testBuildMergeLinkWhenSystemDisabledMailMerge()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->module_dir = 'foobarfoobar';

        $GLOBALS['current_user']->setPreference('mailmerge_on', 'on');

        $settings_cache = sugar_cache_retrieve('admin_settings_cache');
        if (empty($settings_cache)) {
            $settings_cache = [];
        }
        $settings_cache['system_mailmerge_on'] = false;
        sugar_cache_put('admin_settings_cache', $settings_cache);

        $this->assertEmpty(SugarTestReflection::callProtectedMethod($this->lvd, 'buildMergeLink'));

        sugar_cache_clear('admin_settings_cache');
    }

    public function testBuildMergeLinkWhenModuleNotInModulesArray()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->module_dir = 'foobarfoobar';

        $GLOBALS['current_user']->setPreference('mailmerge_on', 'on');

        $settings_cache = sugar_cache_retrieve('admin_settings_cache');
        if (empty($settings_cache)) {
            $settings_cache = [];
        }
        $settings_cache['system_mailmerge_on'] = true;
        sugar_cache_put('admin_settings_cache', $settings_cache);

        $this->assertEmpty(
            SugarTestReflection::callProtectedMethod($this->lvd, 'buildMergeLink', [['foobar' => 'foobar']])
        );

        sugar_cache_clear('admin_settings_cache');
    }

    public function testBuildMergeLink()
    {
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->module_dir = 'foobarfoobar';

        $GLOBALS['current_user']->setPreference('mailmerge_on', 'on');

        $settings_cache = sugar_cache_retrieve('admin_settings_cache');
        if (empty($settings_cache)) {
            $settings_cache = [];
        }
        $settings_cache['system_mailmerge_on'] = true;
        sugar_cache_put('admin_settings_cache', $settings_cache);

        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildMergeLink', [['foobarfoobar' => 'foobarfoobar']]);
        $this->assertStringContainsString('index.php?action=index&module=MailMerge&entire=true', $output);

        sugar_cache_clear('admin_settings_cache');
    }

    public function testBuildTargetLink()
    {
        $_POST['module'] = 'foobar';
        $this->lvd->seed = new stdClass();
        $this->lvd->seed->module_dir = 'foobarfoobar';

        $output = SugarTestReflection::callProtectedMethod($this->lvd, 'buildTargetList');

        $this->assertStringContainsString("input.setAttribute ( 'name' , 'module' );			    input.setAttribute ( 'value' , 'foobarfoobar' );", $output);
        $this->assertStringContainsString("input.setAttribute ( 'name' , 'current_query_by_page' );			    input.setAttribute ( 'value', '" . base64_encode(serialize($_REQUEST)) . "' );", $output);
    }

    public function testDisplayEndWhenNotShowingMassUpdateForm()
    {
        $this->lvd->show_mass_update_form = false;

        $this->assertEmpty($this->lvd->displayEnd());
    }

    public function testDisplayEndWhenShowingMassUpdateForm()
    {
        $this->lvd->show_mass_update_form = true;
        $this->lvd->mass = $this->createMock('MassUpdate');
        $this->lvd->mass->expects($this->any())
            ->method('getMassUpdateForm')
            ->will($this->returnValue('foo'));
        $this->lvd->mass->expects($this->any())
            ->method('endMassUpdateForm')
            ->will($this->returnValue('bar'));

        $this->assertEquals('foobar', $this->lvd->displayEnd());
    }

    public function testGetMultiSelectData()
    {
        $this->lvd->moduleString = 'foobar';

        $output = $this->lvd->getMultiSelectData();

        $this->assertEquals($output, "<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n" .
            "<textarea style='display: none' name='uid'></textarea>\n" .
            "<input type='hidden' name='select_entire_list' value='0'>\n" .
            "<input type='hidden' name='foobar' value='0'>\n" .
            "<input type='hidden' name='show_plus' value=''>\n", $output);
    }

    public function testGetMultiSelectDataWithRequestParameterUidSet()
    {
        $this->lvd->moduleString = 'foobar';
        $_REQUEST['uid'] = '1234';

        $output = $this->lvd->getMultiSelectData();

        $this->assertEquals("<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n" .
            "<textarea style='display: none' name='uid'>1234</textarea>\n" .
            "<input type='hidden' name='select_entire_list' value='0'>\n" .
            "<input type='hidden' name='foobar' value='0'>\n" .
            "<input type='hidden' name='show_plus' value=''>\n", $output);
    }

    public function testGetMultiSelectDataWithRequestParameterSelectEntireListSet()
    {
        $this->lvd->moduleString = 'foobar';
        $_REQUEST['select_entire_list'] = '1234';

        $output = $this->lvd->getMultiSelectData();

        $this->assertEquals("<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n" .
            "<textarea style='display: none' name='uid'></textarea>\n" .
            "<input type='hidden' name='select_entire_list' value='1234'>\n" .
            "<input type='hidden' name='foobar' value='0'>\n" .
            "<input type='hidden' name='show_plus' value=''>\n", $output);
    }

    public function testGetMultiSelectDataWithRequestParameterMassupdateSet()
    {
        $this->lvd->moduleString = 'foobar';
        $_REQUEST['uid'] = '1234';
        $_REQUEST['select_entire_list'] = '5678';
        $_REQUEST['massupdate'] = 'true';

        $output = $this->lvd->getMultiSelectData();

        $this->assertEquals("<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n" .
            "<textarea style='display: none' name='uid'></textarea>\n" .
            "<input type='hidden' name='select_entire_list' value='0'>\n" .
            "<input type='hidden' name='foobar' value='0'>\n" .
            "<input type='hidden' name='show_plus' value=''>\n", $output);
    }

    /**
     * Check setupHTMLFields
     *
     * @dataProvider setupHTMLFieldsDataProvider
     * @param $expected - Expected HTML field value
     * @param $field - Field name
     * @param $displayColumns - Display columns def containing the definition for HTML $field
     */
    public function testSetupHTMLFields($expected, $field, $displayColumns)
    {
        $this->lvd->displayColumns = $displayColumns;

        $this->lvd->seed = new stdClass();
        $this->lvd->seed->custom_fields = new stdClass();
        $this->lvd->seed->custom_fields->bean = new stdClass();
        $this->lvd->seed->custom_fields->bean->test_c = $displayColumns[$field]['default'];

        $data = [
            'data' => [
                0 => [],
            ],
        ];

        $data = SugarTestReflection::callProtectedMethod($this->lvd, 'setupHTMLFields', [$data]);

        $this->assertEquals($expected, $data['data'][0][$field], 'HTML Field value not set');
    }

    public static function setupHTMLFieldsDataProvider()
    {
        return [
            [
                '<p>test</p>',
                'test_c',
                [
                    'test_c' => [
                        'type' => 'html',
                        'default' => '<p>test</p>',
                    ],
                ],
            ],
        ];
    }

    /**
     * bug 50645 Blank value for URL custom field in DetailView and subpanel
     * @dataProvider defaultSeedDefValuesProvider
     */
    public function testDefaultSeedDefValues($expected, $displayColumns, $fieldDefs)
    {
        $this->lvd->displayColumns = $displayColumns;
        $this->lvd->lvd = new stdClass();
        $this->lvd->lvd->seed = new stdClass();
        $this->lvd->lvd->seed->field_defs = $fieldDefs;
        SugarTestReflection::callProtectedMethod($this->lvd, 'fillDisplayColumnsWithVardefs');
        foreach ($this->lvd->displayColumns as $columnName => $def) {
            $seedName = strtolower($columnName);
            $seedDef = $this->lvd->lvd->seed->field_defs[$seedName];
            $this->assertEquals($expected, $seedDef['default'] === $def['default']);
        }
    }

    public static function defaultSeedDefValuesProvider()
    {
        return [
            [
                true,
                [
                    [
                        'default' => true,
                        'label' => 'LBL_TEST_TEST_KEY',
                    ],
                ],
                [
                    [
                        'default' => 'test/url/pattern/{id}',
                        'label' => 'LBL_TEST_TEST_KEY',
                    ],
                ],
            ],
            [
                false,
                [
                    [
                        'default' => false,
                        'label' => 'LBL_TEST_TEST_KEY',
                    ],
                ],
                [
                    [
                        'default' => 'test/url/pattern/{id}',
                        'label' => 'LBL_TEST_TEST_KEY',
                    ],
                ],
            ],
            [
                false,
                [
                    [
                        'default' => false,
                        'label' => 'LBL_TEST_TEST_KEY',
                    ],
                ],
                [
                    [
                        'default' => null,
                        'label' => 'LBL_TEST_TEST_KEY',
                    ],
                ],
            ],
        ];
    }
}
