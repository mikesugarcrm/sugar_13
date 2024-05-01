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
 * Check that actions are filtered depending on view given
 *
 * @ticket 62050
 * @author avucinic@sugarcrm.com
 */
class Bug62050Test extends TestCase
{
    private $files = [];

    protected function setUp(): void
    {
        $this->files = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            unlink($file);
        }
    }

    /**
     * Test Detail View Filter Function
     *
     * @dataProvider dataProvider
     * @group 62050
     */
    public function testDetailViewFilterFunction($module, $action, $view, $path, $data, $allowedActions, $bannedActions)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $file = $path . 'deps.ext.php';

        // Add the files for deletion in tear down
        $this->files[] = $file;

        sugar_file_put_contents($file, $data);

        SugarAutoLoader::buildCache();
        $dependencies = DependencyManager::getModuleDependenciesForAction($module, $action, $view);
        $def = $dependencies[0]->getDefinition();

        // Pull out the filtered actions
        $filteredActions = [];
        foreach ($def['actions'] as $action) {
            $filteredActions[] = $action['action'];
        }

        // Check if all the allowed actions are there
        foreach ($allowedActions as $action) {
            $this->assertContains($action, $filteredActions);
        }

        // Check if the disallowed were removed
        foreach ($bannedActions as $action) {
            $this->assertNotContains($action, $filteredActions);
        }
    }

    public function dataProvider()
    {
        return [
            [
                // Module
                'Opportunities',
                // Action
                'view',
                // View
                'DetailView',
                // Path for the custom dependencies
                'custom/modules/Opportunities/Ext/Dependencies/',
                // Dependencies data
                "<?php \$dependencies['Opportunities']['views'] = array(
                        'hooks' => array('view'),
                        'trigger' => 'equal(\$name, \"aabb\")',
                        'triggerFields' => array('name'),
                        'onload' => true,
                        'actions' => array(
                            array(
                                'name' => 'SetValue',
                                'params' => array(
                                    'target' => 'amount',
                                    'value' => '99999',
                                ),
                            ),
                            array(
                                'name' => 'Style',
                                'params' => array(
                                    'target' => 'amount',
                                    'attrs'  => array(
                                        'fontSize' => '\"16px\"',
                                        'fontWeight' => '\"bold\"',
                                        'color' => '\"red\"',
                                    ),
                                ),
                            ),
                            array(
                                'name' => 'SetRequired',
                                'params' => array(
                                    'target' => 'amount',
                                    'label'  => 'amount',
                                    'value' => 'equal(\$name, \"aabb\")',
                                ),
                            ),
                            array(
                                'name' => 'SetPanelVisibility',
                                'params' => array(
                                    'target' => 'whole_subpanel_activities',
                                    'value'  => 'false',
                                ),
                            ),
                        ),
                    );
                ",
                // Allowed Expression Actions
                [
                    'SetValue',
                    'Style',
                    'SetPanelVisibility',
                ],
                // Banned Expression Actions
                [
                    'SetRequired',
                ],
            ],
        ];
    }
}
