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

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/post/9_CheckOutput.php';

/**
 * Test asserts correct replacing of print_r and var_dump functions in php files under custom directory
 */
class CheckOutputTest extends TestCase
{
    /** @var UpgradeDriver */
    protected $upgradeDriver = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('files');
        $this->upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $this->upgradeDriver->context = [];
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts correct replacing of print_r, var_dump function to custom sugar_upgrade_
     *
     * @param string $content
     * @param string $expected
     * @param array $healthcheck
     * @dataProvider getContents
     */
    public function testRun($content, $expected, $healthcheck)
    {
        $file = sugar_cached(md5(microtime(true))) . '/test.php';
        SugarAutoLoader::ensureDir(dirname($file));
        SugarTestHelper::saveFile($file);
        sugar_file_put_contents($file, $content);

        $script = $this->getMockBuilder('SugarUpgradeCheckOutput')
            ->setMethods(['backupFile'])
            ->setConstructorArgs([$this->upgradeDriver])
            ->getMock();
        if ($content == $expected) {
            $script->expects($this->never())->method('backupFile');
        } else {
            $script->expects($this->atLeastOnce())->method('backupFile')->with($this->equalTo($file));
        }
        foreach ($healthcheck as $k => $v) {
            $healthcheck[$k]['params'] = [
                $file,
            ];
        }
        $this->upgradeDriver->state['healthcheck'] = $healthcheck;
        $script->run();
        $actual = sugar_file_get_contents($file);
        $this->assertEquals($expected, $actual, 'File replaced incorrectly');
    }

    /**
     * Test asserts correct skipping the replacing of print_r, var_dump function to custom sugar_upgrade_
     * in module from BWC list
     *
     * @dataProvider getContentsFail
     */
    public function testRunFail($content, $expected)
    {
        $file = sugar_cached(md5(microtime(true))) . '/test.php';
        SugarAutoLoader::ensureDir(dirname($file));
        SugarTestHelper::saveFile($file);
        sugar_file_put_contents($file, $content);

        $script = $this->getMockBuilder('SugarUpgradeCheckOutput')
            ->setMethods(['backupFile'])
            ->setConstructorArgs([$this->upgradeDriver])
            ->getMock();
        $script->expects($this->never())->method('backupFile');

        $script->run();
        $actual = sugar_file_get_contents($file);
        $this->assertEquals($expected, $actual, 'File replaced incorrectly');
    }

    /**
     * Returns data for testRun, content and its expected replaced version
     *
     * @return array
     */
    public static function getContents()
    {
        return [
            [
                "<?php \n print_r('data');\n?>",
                "<?php \n sugar_upgrade_print_r('data');\n?>",
                [
                    [
                        'report' => 'foundPrintR',
                    ],
                ],
            ],
            [
                "<?php \n print_r('data', true);\n?>",
                "<?php \n print_r('data', true);\n?>",
                [
                    [
                        'report' => 'foundPrintR',
                    ],
                ],
            ],
            [
                "<?php \n print_r('data', true);\n print_r('data');\n?>",
                "<?php \n print_r('data', true);\n sugar_upgrade_print_r('data');\n?>",
                [
                    [
                        'report' => 'foundPrintR',
                    ],
                ],
            ],
            [
                "<?php \n sugar_upgrade_print_r('text');\n?>",
                "<?php \n sugar_upgrade_print_r('text');\n?>",
                [
                    [
                        'report' => 'foundPrintR',
                    ],
                ],
            ],
            [
                "<?php print_r_('text');",
                "<?php print_r_('text');",
                [
                    [
                        'report' => 'foundPrintR',
                    ],
                ],
            ],
            [
                "<?php \n var_dump('data');\n?>",
                "<?php \n sugar_upgrade_var_dump('data');\n?>",
                [
                    [
                        'report' => 'foundVarDump',
                    ],
                ],
            ],
            [
                "<?php \n sugar_upgrade_var_dump('text');\n?>",
                "<?php \n sugar_upgrade_var_dump('text');\n?>",
                [
                    [
                        'report' => 'foundVarDump',
                    ],
                ],
            ],

            [
                "<?php \n exit('data');\n?>",
                "<?php \n sugar_upgrade_exit('data');\n?>",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            [
                "<?php \n sugar_upgrade_exit();\n?>",
                "<?php \n sugar_upgrade_exit();\n?>",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            [
                '<?php exit;',
                '<?php sugar_upgrade_exit;',
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],

            [
                "<?php \n died('data');\n?>",
                "<?php \n died('data');\n?>",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            [
                "<?php \n die('data');\n?>",
                "<?php \n sugar_upgrade_die('data');\n?>",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            [
                "<?php \n sugar_upgrade_die();\n?>",
                "<?php \n sugar_upgrade_die();\n?>",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],

            [
                "<?php \n die('data');\n array('AIHODYN6' => 'DIems');\n?>",
                "<?php \n sugar_upgrade_die('data');\n array('AIHODYN6' => 'DIems');\n?>",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            [
                "<?php \n if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');\n die('data');",
                "<?php \n if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');\n sugar_upgrade_die('data');",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            // Custom message in entryPoint check
            [
                "<?php \n if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point - Custom test message');\n die('data');",
                "<?php \n if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point - Custom test message');\n sugar_upgrade_die('data');",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            // Custom entryPoint check should be changed according to common replacement
            [
                "<?php \n if(empty(\$GLOBALS['sugarEntry'])) die('Not A Valid Entry Point - FIL TESTING');\n",
                "<?php \n if(empty(\$GLOBALS['sugarEntry'])) sugar_upgrade_die('Not A Valid Entry Point - FIL TESTING');\n",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                ],
            ],
            // Complex replace test
            [
                "<?php \n if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); \n print_r('data'); \n print_r('data', true); \n print_r('data', true); \n print_r('data'); \n sugar_upgrade_print_r('text'); \n print_r_('text'); \n var_dump('data'); \n sugar_upgrade_var_dump('text'); \n exit('data'); \n sugar_upgrade_exit(); \n exit; \n died('data'); \n die('data'); \n sugar_upgrade_die(); \n die('data'); \n array('AIHODYN6' => 'DIems');",
                "<?php \n if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); \n sugar_upgrade_print_r('data'); \n print_r('data', true); \n print_r('data', true); \n sugar_upgrade_print_r('data'); \n sugar_upgrade_print_r('text'); \n print_r_('text'); \n sugar_upgrade_var_dump('data'); \n sugar_upgrade_var_dump('text'); \n sugar_upgrade_exit('data'); \n sugar_upgrade_exit(); \n sugar_upgrade_exit; \n died('data'); \n sugar_upgrade_die('data'); \n sugar_upgrade_die(); \n sugar_upgrade_die('data'); \n array('AIHODYN6' => 'DIems');",
                [
                    [
                        'report' => 'foundDieExit',
                    ],
                    [
                        'report' => 'foundPrintR',
                    ],
                    [
                        'report' => 'foundVarDump',
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns data for testRunFail, content and its expected replaced version
     *
     * @return array
     */
    public static function getContentsFail()
    {
        return [
            [
                "<?php \n print_r('data');\n?>",
                "<?php \n print_r('data');\n?>",
            ],
        ];
    }
}
