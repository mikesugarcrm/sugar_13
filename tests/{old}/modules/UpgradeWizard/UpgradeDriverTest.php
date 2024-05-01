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
 * UpgradeDriverTest
 *
 * This class tests functions inside the file UpgradeDriver.php.
 */

require_once 'modules/UpgradeWizard/CliUpgrader.php';

class UpgradeDriverTest extends TestCase
{
    protected $driver;

    protected function setUp(): void
    {
        $this->driver = new CliUpgrader();
    }

    protected function tearDown(): void
    {
        unset($this->driver);
    }

    /**
     * This function tests cases for different combinations of parameters for configs.
     *
     * @param array $old : the old configs from "config.php" before upgrade.
     * @param array $over : the override configs from "config_override.php".
     * @param array $new : the new configs generated during the upgrade.
     * @param array $expected : the expected result of the test case.
     *
     * @dataProvider providers_test_genConfigs
     */
    public function test_genConfigs($old, $over, $new, $expected)
    {
        $this->assertEquals($expected, $this->driver->genConfigs($old, $over, $new));
    }

    /**
     * This function provides inputs for test_genConfigs().
     *
     * @return array the expected values of the test.
     */
    public function providers_test_genConfigs()
    {
        $returnArray = [
            ///////////////////
            //Cases for array
            //////////////////
            [ // Case: Same in $over and $new, but not in $old
                [],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                [],
            ],
            [ // Case: Same in $over and $new, but different in $old
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Feb 15, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Feb 15, 2014']],
            ],
            [ // Case: Same in all three
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
            ],
            [ // Case: Same in $over and $new, but extra elements than $old
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
            ],
            [ // Case: Only in new, but not in either $over or $old
                [],
                [],
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
            ],
            [ // Case: Different in $old and $over, but not in $new
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
                ['WRALholidays' => ['1' => 'Jan 13, 2014']],
                [],
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
            ],
            [ // Case: Incremental in $old, $over, and $new
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
                ['WRALholidays' => ['1' => 'Jan 13, 2014']],
                ['WRALholidays' => ['2' => 'Feb 15, 2014']],
                ['WRALholidays' => ['0' => 'Jan 1, 2014', '2' => 'Feb 15, 2014']],
            ],
            [ // Case: Different values for the same key in $old, $over, and $new
                ['WRALholidays' => ['0' => 'Jan 1, 2014']],
                ['WRALholidays' => ['0' => 'Jan 13, 2014']],
                ['WRALholidays' => ['0' => 'Feb 15, 2014']],
                ['WRALholidays' => ['0' => 'Feb 15, 2014']],
            ],
            ///////////////////
            //Cases for boolean
            //////////////////
            [ // Case: boolean value, same $over and $new but not in $old
                [],
                ['fts_disable_notification' => true],
                ['fts_disable_notification' => true],
                [],
            ],
            [ // Case: boolean value, same in $over and new, but different in $old
                ['fts_disable_notification' => false],
                ['fts_disable_notification' => true],
                ['fts_disable_notification' => true],
                ['fts_disable_notification' => false],
            ],
            [ // Case: boolean value, only in $new but not in $over or $old
                [],
                [],
                ['fts_disable_notification' => true],
                ['fts_disable_notification' => true],
            ],
            ///////////////////
            //Cases for string
            //////////////////
            [ // Case: string value, same $over and $new but not in $old
                [],
                ['chartEngine' => 'bar'],
                ['chartEngine' => 'bar'],
                [],
            ],
            [ // Case: string value, same in $over and new, but different in $old
                ['chartEngine' => 'foo'],
                ['chartEngine' => 'bar'],
                ['chartEngine' => 'bar'],
                ['chartEngine' => 'foo'],
            ],
            [ // Case: string value, only in $new but not in $over or $old
                [],
                [],
                ['chartEngine' => 'baz'],
                ['chartEngine' => 'baz'],
            ],
            [ // Case: string value, Different in everything
                ['chartEngine' => 'foo'],
                ['chartEngine' => 'bar'],
                ['chartEngine' => 'paz'],
                ['chartEngine' => 'paz'],
            ],
            ///////////////////
            //Cases for number
            //////////////////
            [ // Case: number value, same $over and $new but not in $old
                [],
                ['js_lang_version' => 2],
                ['js_lang_version' => 2],
                [],
            ],
            [ // Case: number value, same in $over and new, but different in $old
                ['js_lang_version' => 1],
                ['js_lang_version' => 2],
                ['js_lang_version' => 2],
                ['js_lang_version' => 1],
            ],
            [ // Case: number value, only in $new but not in $over or $old
                [],
                [],
                ['js_lang_version' => 2],
                ['js_lang_version' => 2],
            ],
            [ // Case: number value, different int everything
                ['js_lang_version' => 1],
                ['js_lang_version' => 2],
                ['js_lang_version' => 3],
                ['js_lang_version' => 3],
            ],
            ///////////////////
            //Cases for deep array
            //////////////////
            [ // Case: same $over and $new but not in $old
                [],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
                [],
            ],
            [ // Case: same in $over and new, but different in $old
                ['foo' => ['bar1' => ['1' => '100'], 'bar2' => ['2' => '200']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
                ['foo' => ['bar1' => ['1' => '100'], 'bar2' => ['2' => '200']]],
            ],
            [ // Case: only in $new but not in $over or $old
                [],
                [],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
            ],
            [ // Case: $new and $over have different values in deep level
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['3' => '30']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['3' => '30']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
            ],
            [ // Case: $new and $over have incremental values in deep level
                ['foo' => ['bar1' => ['1' => '10']]],
                ['foo' => ['bar1' => ['1' => '10', '3' => '30']]],
                ['foo' => ['bar1' => ['1' => '10', '3' => '30']]],
                ['foo' => ['bar1' => ['1' => '10']]],
            ],
            [ // Case: $new has values in deep level than $old, and $over is empty
                ['foo' => ['bar1' => ['1' => '10']]],
                [],
                ['foo' => ['bar1' => ['1' => '10', '3' => '30']]],
                ['foo' => ['bar1' => ['1' => '10', '3' => '30']]],
            ],
            [ // Case: $old, $over and $new have different values in deep level
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '20']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '30']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '50']]],
                ['foo' => ['bar1' => ['1' => '10'], 'bar2' => ['2' => '50']]],
            ],
        ];
        return $returnArray;
    }
}
