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
 * Class Bug65942Test
 *
 * Test if saveLabels saved multiple labels for same module properly
 *
 * @author avucinic@sugarcrm.com
 */
class Bug65942Test extends TestCase
{
    private $path = 'custom/Extension/modules/relationships';
    private $files = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();

        foreach ($this->files as $file) {
            unlink($file);
        }
    }

    /**
     * @param $labelDefinitions -  Label Definitions
     * @param $testLabel - Test if this label was saved
     *
     * @group Bug65942
     * @dataProvider getLabelDefinitions
     */
    public function testIfAllLabelsSaved($labelDefinitions, $testLabel, $relationshipName, $fileName)
    {
        $abstractRelationships = new AbstractRelationships65942Test();
        $abstractRelationships->saveLabels(
            $this->path,
            '',
            $relationshipName,
            $labelDefinitions
        );

        $generatedLabels = file_get_contents($this->path . '/language/' . $fileName);
        $this->files[] = $this->path . '/language/' . $fileName;

        $this->assertStringContainsString($testLabel, $generatedLabels);
    }

    public static function getLabelDefinitions()
    {
        return [
            [
                [
                    0 => [
                        'module' => 'Bug65942Test',
                        'system_label' => 'LBL_65942_TEST_1',
                        'display_label' => 'Bug65942Test 1',
                    ],
                    1 => [
                        'module' => 'Bug65942Test',
                        'system_label' => 'LBL_65942_TEST_2',
                        'display_label' => 'Bug65942Test 2',
                    ],
                ],
                '$mod_strings[\'LBL_65942_TEST_1\'] = \'Bug65942Test 1\';',
                null,
                'Bug65942Test.php',
            ],
            [
                [
                    0 => [
                        'module' => '65942Test',
                        'system_label' => '65942_TEST_1',
                        'display_label' => '65942Test 1',
                    ],
                    1 => [
                        'module' => '65942Test',
                        'system_label' => '65942_TEST_2',
                        'display_label' => '65942Test 2',
                    ],
                    2 => [
                        'module' => '65942Test',
                        'system_label' => '65942_TEST_3',
                        'display_label' => '65942Test 3',
                    ],
                ],
                '$mod_strings[\'65942_TEST_2\'] = \'65942Test 2\';',
                'named_relation_1',
                '65942Test.named_relation_1.php',
            ],
        ];
    }
}

/**
 * Class AbstractRelationships65942Test
 *
 * Test Helper class, override saveLabels so we can test it
 */
class AbstractRelationships65942Test extends AbstractRelationships
{
    public function saveLabels($basepath, $installDefPrefix, $relationshipName, $labelDefinitions)
    {
        return parent::saveLabels($basepath, $installDefPrefix, $relationshipName, $labelDefinitions);
    }
}
