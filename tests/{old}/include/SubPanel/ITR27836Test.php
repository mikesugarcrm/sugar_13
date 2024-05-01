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
 * @itr 27836
 */
class ITR27836Test extends TestCase
{
    protected $bean;

    protected function setUp(): void
    {
        global $moduleList, $beanList, $beanFiles;
        require 'include/modules.php';
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = new Contact();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        require_once 'ModuleInstall/ModuleInstaller.php';
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true; // make sure that the ModuleInstaller->log() function doesn't echo while rebuilding the layoutdefs
        $moduleInstaller->rebuild_layoutdefs();
    }


    public function subpanelProvider()
    {
        return [
            //Hidden set to true

            [
                'data' => [
                    'testpanel' => [
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => [],
                    ],
                    'default_hidden' => true,
                    'subpanel_name' => 'history',
                    'module' => 'Contacts',
                ],
            ],

            //Hidden set to false

            [
                'data' => [
                    'testpanel' => [
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => [],
                    ],
                    'default_hidden' => false,
                    'subpanel_name' => 'history',
                    'module' => 'Contacts',
                ],
            ],

            //Hidden not set
            [
                'data' => [
                    'testpanel' => [
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => [],
                    ],
                    'subpanel_name' => 'history',
                    'module' => 'Contacts',
                ],
            ],
        ];
    }

    /**
     * testSubpanelDisplay
     *
     * @dataProvider subpanelProvider
     */
    public function testSubPanelDisplay($subpanel)
    {
        $subpanel_def = new aSubPanel('testpanel', $subpanel, $this->bean);

        if (isset($subpanel['default_hidden']) && $subpanel['default_hidden'] === true) {
            $this->assertTrue($subpanel_def->isDefaultHidden());
        } else {
            $this->assertFalse($subpanel_def->isDefaultHidden());
        }
    }
}
