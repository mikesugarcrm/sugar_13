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

class Bug58774Test extends TestCase
{
    private $originalRequest = [];
    private $originalDictionary = [];
    private $backedUpFiles = [];
    private $tearDownFiles = [
        'custom/modules/Calls/Ext/Vardefs/vardefs.ext.php',
        'custom/modules/Calls/metadata/SearchFields.php',
        'custom/Extension/modules/Calls/Ext/Vardefs/sugarfield_duration_hours.php',
        'cache/modules/Calls/Callvardefs.php',
    ];

    protected function setUp(): void
    {
        if (isset($GLOBALS['dictionary']['Call'])) {
            $this->originalDictionary = $GLOBALS['dictionary']['Call'];
        }

        // Back up any current files we might have
        foreach ($this->tearDownFiles as $file) {
            if (file_exists($file)) {
                rename($file, str_replace('.php', '-unittestbackup', $file));
                $this->backedUpFiles[] = $file;
            }
        }

        // The current user needs to be an admin user
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

        $this->originalRequest = ['r' => $_REQUEST, 'p' => $_POST];
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->originalRequest['r'];
        $_POST = $this->originalRequest['p'];

        SugarTestHelper::tearDown();

        // Remove created files
        foreach ($this->tearDownFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // Restore our backups
        foreach ($this->backedUpFiles as $file) {
            rename(str_replace('.php', '-unittestbackup', $file), $file);
        }

        // Reset the dictionary
        if (!empty($this->originalDictionary)) {
            $GLOBALS['dictionary']['Call'] = $this->originalDictionary;
        }
    }

    public function testCacheClearedAfterSavingFieldChanges()
    {
        // Setup some of the items needed in the request
        $_REQUEST = $_POST = [
            'module' => 'ModuleBuilder',
            'action' => 'saveSugarField',
            'view_module' => 'Calls',
            'type' => 'int',
            'name' => 'duration_minutes',
            'labelValue' => 'Duration Minutes:',
            'label' => 'LBL_DURATION_MINUTES',
            'comments' => 'Call duration, minutes portion',
            'min' => '5',
            'max' => '90',
        ];

        $controller = new ModuleBuilderController();
        $controller->action_saveSugarField();

        $newdefs = $this->getNewVardefFromCache();

        // Handle assertions
        $this->assertNotEmpty($newdefs, 'New vardef was not found');
        $this->assertTrue(isset($newdefs['fields']['duration_minutes']), 'duration_minutes field not found in the vardef');
        $this->assertArrayHasKey('min', $newdefs['fields']['duration_minutes'], 'Min value not saved');
        $this->assertEquals(5, $newdefs['fields']['duration_minutes']['min'], 'Min did not save its value properly');
        $this->assertArrayHasKey('max', $newdefs['fields']['duration_minutes'], 'Max value not saved');
        $this->assertEquals(90, $newdefs['fields']['duration_minutes']['max'], 'Max did not save its value properly');
    }

    private function getNewVardefFromCache()
    {
        VardefManager::loadVardef('Calls', 'Call', true);
        return $GLOBALS['dictionary']['Call'];
    }
}
