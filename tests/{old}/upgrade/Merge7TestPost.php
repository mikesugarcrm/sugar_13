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

class Merge7TestPost extends UpgradeTestCase
{
    protected $new_dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->upgrader->setVersions('7.0.0', 'ent', '7.2.0', 'ent');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        rmdir_recursive('modules/Accounts/clients/test');
        rmdir_recursive('custom/modules/Accounts/clients/test');
    }


    protected function createView($viewname, $data, $prefix = '')
    {
        $filename = "modules/Accounts/clients/test/views/$viewname/$viewname.php";
        if ($prefix) {
            $filename = "$prefix/$filename";
        }
        $pdata = ['panels' => $data];
        mkdir_recursive(dirname($filename));
        SugarTestHelper::saveFile($filename);
        write_array_to_file("viewdefs['Accounts']['test']['view']['$viewname']", $pdata, $filename);
    }

    public function mergeData()
    {
        return [
            // add field with out panel name, but panel label
            [
                // pre
                [
                    [
                        'label' => 'panel1',
                        'fields' => ['email', 'phone', 'fax'],
                    ],
                ],
                // post
                [
                    [
                        'label' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', 'description'],
                    ],
                ],
                // custom
                [
                    [
                        'label' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', 'custom_c'],
                    ],
                ],
                // result
                [
                    [
                        'label' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', 'custom_c', 'description'],
                    ],
                ],
            ],
            // add field
            [
                // pre
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'fax'],
                    ],
                ],
                // post
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', 'description'],
                    ],
                ],
                // custom
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', 'custom_c'],
                    ],
                ],
                // result
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', 'custom_c', 'description'],
                    ],
                ],
            ],
            // add field to another panel
            [
                // pre
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email'],
                    ],
                    [
                        'name' => 'panel2',
                        'fields' => ['phone', 'fax'],
                    ],
                ],
                // post
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email'],
                    ],
                    [
                        'name' => 'panel2',
                        'fields' => ['phone', 'fax', ['name' => 'description', 'type' => 'text']],
                    ],
                ],
                // custom
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['custom_c'],
                    ],
                    [
                        'name' => 'panel2',
                        'fields' => ['phone', 'fax'],
                    ],
                ],
                // result
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['custom_c'],
                    ],
                    [
                        'name' => 'panel2',
                        'fields' => ['phone', 'fax', ['name' => 'description', 'type' => 'text']],
                    ],
                ],
            ],
            // remove field
            [
                // pre
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', ['name' => 'address']],
                    ],
                ],
                // post
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'description'],
                    ],
                ],
                // custom
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'fax', 'custom_c'],
                    ],
                    [
                        'name' => 'panel2',
                        'fields' => [['name' => 'address']],
                    ],
                ],
                // result
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', 'custom_c', 'description'],
                    ],
                    [
                        'name' => 'panel2',
                        'fields' => [],
                    ],
                ],
            ],
            // field changed in new
            [
                // pre
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', ['name' => 'fax', 'type' => 'text']],
                    ],
                ],
                // post
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', ['name' => 'fax', 'type' => 'phone'], 'description'],
                    ],
                ],
                // custom
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', ['name' => 'fax', 'type' => 'text'], 'custom_c'],
                    ],
                ],
                // result
                [
                    [
                        'name' => 'panel1',
                        'fields' => [
                            'email', 'phone', ['name' => 'fax', 'type' => 'phone'], 'custom_c', 'description',
                        ],
                    ],
                ],
            ],
            // field changed in custom
            [
                // pre
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', ['name' => 'fax', 'type' => 'text']],
                    ],
                ],
                // post
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', ['name' => 'fax', 'type' => 'phone'], 'description'],
                    ],
                ],
                // custom
                [
                    [
                        'name' => 'panel1',
                        'fields' => ['email', 'phone', ['name' => 'fax', 'type' => 'enum'], 'custom_c'],
                    ],
                ],
                // result
                [
                    [
                        'name' => 'panel1',
                        'fields' => [
                            'email', 'phone', ['name' => 'fax', 'type' => 'enum'], 'custom_c', 'description',
                        ],
                    ],
                ],
            ],

        ];
    }

    /**
     * Test for Merge7Templates
     * @dataProvider mergeData
     */
    public function testMerge7Pre($pre_data, $post_data, $custom_data, $result)
    {
        $viewdefs = [];
        $this->createView('mergetest', $post_data);
        $this->createView('mergetest', $custom_data, 'custom');
        $this->upgrader->state['for_merge']['modules/Accounts/clients/test/views/mergetest/mergetest.php']['Accounts']['test']['view']['mergetest']['panels'] = $pre_data;

        $script = $this->upgrader->getScript('post', '7_Merge7Templates');
        $script->run();
        $this->assertFileExists('custom/modules/Accounts/clients/test/views/mergetest/mergetest.php');
        include 'custom/modules/Accounts/clients/test/views/mergetest/mergetest.php';
        $this->assertEquals($result, $viewdefs['Accounts']['test']['view']['mergetest']['panels']);
    }

    /**
     * Tests merging of non panel defs in the merge upgrader
     *
     * @param array $old Old viewdefs
     * @param array $new New viewdefs
     * @param array $cst Custom viewdefs
     * @param boolean $noChange If there are changes to be picked up
     * @param boolean $needSave If the changes require a save
     * @param array $exp Expected result
     *
     * @dataProvider getMergeTestData
     */
    public function testMergeOtherDefs($old, $new, $cst, $noChange, $needSave, $exp)
    {
        $oldDefs = [];
        $newDefs = [];
        $cstDefs = [];
        $expect = [];
        // Set some stuff that both the test and the upgrader need
        $module = 'Test1';
        $client = 'foo';
        $view = 'bar';

        // Get the script and set some vars
        $script = $this->upgrader->getScript('post', '7_Merge7Templates');
        $script->moduleName = $module;
        $script->clientType = $client;
        $script->viewName = $view;

        // Test change checker... for testing, this should always be false
        $test = $script->defsUnchanged($old, $new, $cst);
        $this->assertEquals($noChange, $test, 'Unexpected defsUnchanged result');

        // Make "real" defs out of the test data
        $oldDefs[$module][$client]['view'][$view] = $old;
        $newDefs[$module][$client]['view'][$view] = $new;
        $cstDefs[$module][$client]['view'][$view] = $cst;

        // Set the actual expected to the full array path
        $expect[$module][$client]['view'][$view] = $exp;

        // Test merge piece
        $actual = $script->mergeOtherDefs($oldDefs, $newDefs, $cstDefs);
        $this->assertEquals($expect, $actual);

        // Test the save flag... this should always be true
        $this->assertEquals($needSave, $script->needSave, 'Unexpected needSave result');
    }

    /**
     * Tests sanitization of viewdefs at the top level after merge.
     *
     * @param array $input
     * @param array $expect
     * @dataProvider getSantizeDataProvider
     * @see          SC-5024
     */
    public function testSanitizeTopLevelDefElements($input, $expect)
    {
        $script = $this->upgrader->getScript('post', '7_Merge7Templates');
        $actual = $script->sanitizeTopLevelDefElements($input);
        $this->assertEquals($actual, $expect);
    }

    public function getSantizeDataProvider()
    {
        return [
            // Test empty top level viewdefs element is removed
            [
                'input' => [
                    'buttons' => [],
                    'panels' => [
                        [
                            'name' => 'panel1',
                            'fields' => [
                                'test1',
                                'test2',
                            ],
                        ],
                    ],
                    'templateMeta' => [
                        'maxColumns' => '1',
                        'widths' => [
                            [
                                'label' => '10',
                                'field' => '30',
                            ],
                        ],
                    ],
                ],
                'expect' => [
                    'panels' => [
                        [
                            'name' => 'panel1',
                            'fields' => [
                                'test1',
                                'test2',
                            ],
                        ],
                    ],
                    'templateMeta' => [
                        'maxColumns' => '1',
                        'widths' => [
                            [
                                'label' => '10',
                                'field' => '30',
                            ],
                        ],
                    ],
                ],
            ],
            // Test not empty top level viewdefs element not changed
            [
                'input' => [
                    'buttons' => ['Test not empty'],
                    'panels' => [
                        [
                            'name' => 'panel1',
                            'fields' => [
                                'test1',
                                'test2',
                            ],
                        ],
                    ],
                    'templateMeta' => [
                        'maxColumns' => '1',
                        'widths' => [
                            [
                                'label' => '10',
                                'field' => '30',
                            ],
                        ],
                    ],
                ],
                'expect' => [
                    'buttons' => ['Test not empty'],
                    'panels' => [
                        [
                            'name' => 'panel1',
                            'fields' => [
                                'test1',
                                'test2',
                            ],
                        ],
                    ],
                    'templateMeta' => [
                        'maxColumns' => '1',
                        'widths' => [
                            [
                                'label' => '10',
                                'field' => '30',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getMergeTestData()
    {
        return [
            // Old/new same, no custom... take old/new
            [
                'old' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ],
                        [
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ],
                    ],
                ],
                'new' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ],
                        [
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ],
                    ],
                ],
                'cst' => [],
                'noChange' => true,
                'needSave' => false,
                'expect' => [],
            ],
            // Old/New same, Custom different, merge custom with changes
            [
                'old' => [
                    'a1' => [
                        'id' => 'record_view',
                        'defaults' => [
                            'show_more' => 'more',
                        ],
                    ],
                    'a2' => [
                        'node' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'new' => [
                    'a1' => [
                        'id' => 'record_view',
                        'defaults' => [
                            'show_more' => 'more',
                        ],
                    ],
                    'a2' => [
                        'node' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'cst' => [
                    'a1' => [
                        'id' => 'list_view',
                    ],
                ],
                'noChange' => false,
                'needSave' => false,
                'expect' => [
                    'a1' => [
                        'id' => 'list_view',
                    ],
                ],
            ],
            // Old/New different, no Custom, take changes between new and old
            [
                'old' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ],
                        [
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ],
                    ],
                ],
                'new' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'modify_button',
                            'label' => 'LBL_MODIFY_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                        ],
                    ],
                ],
                'cst' => [],
                'noChange' => false,
                'needSave' => false,
                'expect' => [],
            ],
            // Old, new and Custom all different, merge custom with changes
            [
                'old' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ],
                        [
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ],
                    ],
                ],
                'new' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'modify_button',
                            'label' => 'LBL_MODIFY_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                        ],
                    ],
                ],
                'cst' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'send_button',
                            'label' => 'LBL_SEND_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => ['edit', 'record'],
                        ],
                    ],
                ],
                'noChange' => false,
                'needSave' => true,
                'expect' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'modify_button',
                            'label' => 'LBL_MODIFY_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                        ],
                        [
                            'type' => 'button',
                            'name' => 'send_button',
                            'label' => 'LBL_SEND_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => ['edit', 'record'],
                        ],
                    ],
                ],
            ],
            // From ticket # BR-1804...
            // old is 7.2.0 OOTB Accounts record viewdefs
            // new is 7.2.1 OOTB Accounts record viewdefs
            // cst is 7.2.0 custom Accounts record viewdefs
            // expect contains panels, last_state and buttons
            [
                'old' => [
                    'panels' => [
                        [
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => [
                                [
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ],
                                'name',
                                [
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ],
                                [
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ],
                            ],
                        ],
                        [
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => [
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ],
                        ],
                        [
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'hide' => true,
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => [
                                [
                                    'name' => 'billing_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_BILLING_ADDRESS',
                                    'fields' => [
                                        [
                                            'name' => 'billing_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STREET',
                                        ],
                                        [
                                            'name' => 'billing_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_CITY',
                                        ],
                                        [
                                            'name' => 'billing_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STATE',
                                        ],
                                        [
                                            'name' => 'billing_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                                        ],
                                        [
                                            'name' => 'billing_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_COUNTRY',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'shipping_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_SHIPPING_ADDRESS',
                                    'fields' => [
                                        [
                                            'name' => 'shipping_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STREET',
                                        ],
                                        [
                                            'name' => 'shipping_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_CITY',
                                        ],
                                        [
                                            'name' => 'shipping_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STATE',
                                        ],
                                        [
                                            'name' => 'shipping_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
                                        ],
                                        [
                                            'name' => 'shipping_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
                                        ],
                                        [
                                            'name' => 'copy',
                                            'label' => 'NTC_COPY_BILLING_ADDRESS',
                                            'type' => 'copy',
                                            'mapping' => [
                                                'billing_address_street' => 'shipping_address_street',
                                                'billing_address_city' => 'shipping_address_city',
                                                'billing_address_state' => 'shipping_address_state',
                                                'billing_address_postalcode' => 'shipping_address_postalcode',
                                                'billing_address_country' => 'shipping_address_country',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'phone_alternate',
                                    'label' => 'LBL_OTHER_PHONE',
                                ],
                                'email',
                                'phone_fax',
                                'campaign_name',
                                'twitter',
                                [
                                    'name' => 'description',
                                    'span' => 12,
                                ],
                                'sic_code',
                                'ticker_symbol',
                                'annual_revenue',
                                'employees',
                                'ownership',
                                'rating',

                                [
                                    'name' => 'duns_num',
                                    'readonly' => true,
                                ],
                                [
                                    'name' => 'date_entered_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_ENTERED',
                                    'fields' => [
                                        [
                                            'name' => 'date_entered',
                                        ],
                                        [
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ],
                                        [
                                            'name' => 'created_by_name',
                                        ],
                                    ],
                                ],
                                'team_name',
                                [
                                    'name' => 'date_modified_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_MODIFIED',
                                    'fields' => [
                                        [
                                            'name' => 'date_modified',
                                        ],
                                        [
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ],
                                        [
                                            'name' => 'modified_by_name',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'new' => [
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ],
                        [
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ],
                        [
                            'type' => 'actiondropdown',
                            'name' => 'main_dropdown',
                            'primary' => true,
                            'showOn' => 'view',
                            'buttons' => [
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:edit_button:click',
                                    'name' => 'edit_button',
                                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                                    'acl_action' => 'edit',
                                ],
                                [
                                    'type' => 'shareaction',
                                    'name' => 'share',
                                    'label' => 'LBL_RECORD_SHARE_BUTTON',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'pdfaction',
                                    'name' => 'download-pdf',
                                    'label' => 'LBL_PDF_VIEW',
                                    'action' => 'download',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'pdfaction',
                                    'name' => 'email-pdf',
                                    'label' => 'LBL_PDF_EMAIL',
                                    'action' => 'email',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'divider',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:find_duplicates_button:click',
                                    'name' => 'find_duplicates_button',
                                    'label' => 'LBL_DUP_MERGE',
                                    'acl_action' => 'edit',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:duplicate_button:click',
                                    'name' => 'duplicate_button',
                                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                                    'acl_module' => 'Accounts',
                                    'acl_action' => 'create',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:historical_summary_button:click',
                                    'name' => 'historical_summary_button',
                                    'label' => 'LBL_HISTORICAL_SUMMARY',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:audit_button:click',
                                    'name' => 'audit_button',
                                    'label' => 'LNK_VIEW_CHANGE_LOG',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'divider',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:delete_button:click',
                                    'name' => 'delete_button',
                                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                                    'acl_action' => 'delete',
                                ],
                            ],
                        ],
                        [
                            'name' => 'sidebar_toggle',
                            'type' => 'sidebartoggle',
                        ],
                    ],
                    'panels' => [
                        [
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => [
                                [
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ],
                                'name',
                                [
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ],
                                [
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ],
                            ],
                        ],
                        [
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => [
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ],
                        ],
                        [
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'hide' => true,
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => [
                                [
                                    'name' => 'billing_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_BILLING_ADDRESS',
                                    'fields' => [
                                        [
                                            'name' => 'billing_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STREET',
                                        ],
                                        [
                                            'name' => 'billing_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_CITY',
                                        ],
                                        [
                                            'name' => 'billing_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STATE',
                                        ],
                                        [
                                            'name' => 'billing_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                                        ],
                                        [
                                            'name' => 'billing_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_COUNTRY',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'shipping_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_SHIPPING_ADDRESS',
                                    'fields' => [
                                        [
                                            'name' => 'shipping_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STREET',
                                        ],
                                        [
                                            'name' => 'shipping_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_CITY',
                                        ],
                                        [
                                            'name' => 'shipping_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STATE',
                                        ],
                                        [
                                            'name' => 'shipping_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
                                        ],
                                        [
                                            'name' => 'shipping_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
                                        ],
                                        [
                                            'name' => 'copy',
                                            'label' => 'NTC_COPY_BILLING_ADDRESS',
                                            'type' => 'copy',
                                            'mapping' => [
                                                'billing_address_street' => 'shipping_address_street',
                                                'billing_address_city' => 'shipping_address_city',
                                                'billing_address_state' => 'shipping_address_state',
                                                'billing_address_postalcode' => 'shipping_address_postalcode',
                                                'billing_address_country' => 'shipping_address_country',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'phone_alternate',
                                    'label' => 'LBL_OTHER_PHONE',
                                ],
                                'email',
                                'phone_fax',
                                'campaign_name',
                                'twitter',
                                [
                                    'name' => 'description',
                                    'span' => 12,
                                ],
                                'sic_code',
                                'ticker_symbol',
                                'annual_revenue',
                                'employees',
                                'ownership',
                                'rating',

                                [
                                    'name' => 'duns_num',
                                    'readonly' => true,
                                ],
                                [
                                    'name' => 'date_entered_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_ENTERED',
                                    'fields' => [
                                        [
                                            'name' => 'date_entered',
                                        ],
                                        [
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ],
                                        [
                                            'name' => 'created_by_name',
                                        ],
                                    ],
                                ],
                                'team_name',
                                [
                                    'name' => 'date_modified_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_MODIFIED',
                                    'fields' => [
                                        [
                                            'name' => 'date_modified',
                                        ],
                                        [
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ],
                                        [
                                            'name' => 'modified_by_name',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'cst' => [
                    'panels' => [
                        [
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => [
                                [
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ],
                                'name',
                                [
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ],
                                [
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ],
                            ],
                        ],
                        [
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => [
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ],
                        ],
                    ],
                    'last_state' => [
                        'id' => 'record_view',
                        'defaults' => [
                            'show_more' => 'more',
                        ],
                    ],
                ],
                'noChange' => false,
                'needSave' => true,
                'expect' => [
                    'panels' => [
                        [
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => [
                                [
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ],
                                'name',
                                [
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ],
                                [
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ],
                            ],
                        ],
                        [
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => [
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ],
                        ],
                    ],
                    'buttons' => [
                        [
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ],
                        [
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ],
                        [
                            'type' => 'actiondropdown',
                            'name' => 'main_dropdown',
                            'primary' => true,
                            'showOn' => 'view',
                            'buttons' => [
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:edit_button:click',
                                    'name' => 'edit_button',
                                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                                    'acl_action' => 'edit',
                                ],
                                [
                                    'type' => 'shareaction',
                                    'name' => 'share',
                                    'label' => 'LBL_RECORD_SHARE_BUTTON',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'pdfaction',
                                    'name' => 'download-pdf',
                                    'label' => 'LBL_PDF_VIEW',
                                    'action' => 'download',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'pdfaction',
                                    'name' => 'email-pdf',
                                    'label' => 'LBL_PDF_EMAIL',
                                    'action' => 'email',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'divider',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:find_duplicates_button:click',
                                    'name' => 'find_duplicates_button',
                                    'label' => 'LBL_DUP_MERGE',
                                    'acl_action' => 'edit',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:duplicate_button:click',
                                    'name' => 'duplicate_button',
                                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                                    'acl_module' => 'Accounts',
                                    'acl_action' => 'create',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:historical_summary_button:click',
                                    'name' => 'historical_summary_button',
                                    'label' => 'LBL_HISTORICAL_SUMMARY',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:audit_button:click',
                                    'name' => 'audit_button',
                                    'label' => 'LNK_VIEW_CHANGE_LOG',
                                    'acl_action' => 'view',
                                ],
                                [
                                    'type' => 'divider',
                                ],
                                [
                                    'type' => 'rowaction',
                                    'event' => 'button:delete_button:click',
                                    'name' => 'delete_button',
                                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                                    'acl_action' => 'delete',
                                ],
                            ],
                        ],
                        [
                            'name' => 'sidebar_toggle',
                            'type' => 'sidebartoggle',
                        ],
                    ],
                    'last_state' => [
                        'id' => 'record_view',
                        'defaults' => [
                            'show_more' => 'more',
                        ],
                    ],
                ],
            ],
        ];
    }
}
