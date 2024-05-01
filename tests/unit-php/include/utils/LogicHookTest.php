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

namespace Sugarcrm\SugarcrmTestsUnit\inc\utils;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * LogicHook tests
 * @coversDefaultClass \LogicHook
 */
class LogicHookTest extends TestCase
{
    /**
     * @var mixed
     */
    public $trigger;
    /**
     * @var array List of files/directories to cleanup
     */
    protected $toDelete = [];

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        // backup trigger global
        if (isset($GLOBALS['trigger'])) {
            $this->trigger = $GLOBALS['trigger'];
        }

        $this->toDelete = [];
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        // remove files from disk and autoloader map
        foreach ($this->toDelete as $file) {
            unlink($file);
        }

        // restore trigger global
        if (property_exists($this, 'trigger')) {
            $GLOBALS['trigger'] = $this->trigger;
        } else {
            if (isset($GLOBALS['trigger'])) {
                unset($GLOBALS['trigger']);
            }
        }
    }

    /**
     * Test logic hook triggers
     * @covers ::process_hooks
     * @dataProvider dataProviderTestProcessHooks
     *
     * @param string $file Filename
     * @param string $contents File contents
     * @param array $hookArray Hook array definition
     * @param boolean $useBean Use bean context
     */
    public function testProcessHooks($file, $contents, array $hookArray, $useBean)
    {
        // Global variable to track logic hook trigger
        $GLOBALS['trigger'] = [];

        // Create test file
        $this->createFile($file, $contents);

        // Setup logic hook
        $lh = new \LogicHook();

        // Attach bean context
        if ($useBean) {
            $bean = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->getMock();
            $lh->bean = $bean;
        } else {
            $lh->bean = null;
        }

        // Process logic hook
        $random = md5(microtime());
        $hookArray = ['after_save' => [$hookArray]];
        $lh->process_hooks($hookArray, 'after_save', [$random]);

        // Setup expectations and test results
        $expected = ['after_save', [$random]];
        if ($useBean) {
            array_unshift($expected, $bean);
        }
        $this->assertSame($expected, $GLOBALS['trigger']);
    }

    public function dataProviderTestProcessHooks()
    {
        return [
            // Hook class = Hook function without bean context
            [
                'Trigger1.php',
                $this->getClassContent(
                    'MyLogicHook1',
                    null,
                    'public function __construct($event, $args) {
                        $GLOBALS["trigger"] = array($event, $args);
                    }'
                ),
                [
                    0 => 1,
                    1 => 'doStuff1',
                    2 => 'Trigger1.php',
                    3 => 'MyLogicHook1',
                    4 => 'MyLogicHook1',
                ],
                false,
            ],
            // Hook class = Hook function with bean context
            [
                'Trigger2.php',
                $this->getClassContent(
                    'MyLogicHook2',
                    null,
                    'public function __construct($bean, $event, $args) {
                        $GLOBALS["trigger"] = array($bean, $event, $args);
                    }'
                ),
                [
                    0 => 1,
                    1 => 'doStuff2',
                    2 => 'Trigger2.php',
                    3 => 'MyLogicHook2',
                    4 => 'MyLogicHook2',
                ],
                true,
            ],
            // Hook class <> Hook function without bean context
            [
                'Trigger3.php',
                $this->getClassContent(
                    'MyLogicHook3',
                    null,
                    'public function callMe($event, $args) {
                        $GLOBALS["trigger"] = array($event, $args);
                    }'
                ),
                [
                    0 => 1,
                    1 => 'doStuff3',
                    2 => 'Trigger3.php',
                    3 => 'MyLogicHook3',
                    4 => 'callMe',
                ],
                false,
            ],
            // Hook class <> Hook function with bean context
            [
                'Trigger4.php',
                $this->getClassContent(
                    'MyLogicHook4',
                    null,
                    'public function callMe($bean, $event, $args) {
                        $GLOBALS["trigger"] = array($bean, $event, $args);
                    }'
                ),
                [
                    0 => 1,
                    1 => 'doStuff4',
                    2 => 'Trigger4.php',
                    3 => 'MyLogicHook4',
                    4 => 'callMe',
                ],
                true,
            ],
        ];
    }

    /**
     * @covers ::getProcessOrder
     * @dataProvider dataProviderTestGetProcessOrder
     *
     * @param array $hookArray
     * @param array $expected
     */
    public function testGetProcessOrder(array $hookArray, array $expected)
    {
        $lh = new \LogicHook();
        $result = TestReflection::callProtectedMethod($lh, 'getProcessOrder', [$hookArray]);
        $this->assertSame($expected, $result);
    }

    public function dataProviderTestGetProcessOrder()
    {
        return [
            // Empty check
            [
                [],
                [],
            ],
            // Full example
            [
                [
                    0 => [
                        0 => 1,
                        1 => 'activitystream',
                        2 => 'modules/ActivityStream/Activities/ActivityQueueManager.php',
                        3 => 'ActivityQueueManager',
                        4 => 'eventDispatcher',
                    ],
                    1 => [
                        0 => 3,
                        1 => 'fts',
                        2 => 'modules/pmse_Inbox/engine/PMSELogicHook.php',
                        3 => 'PMSELogicHook',
                        4 => 'after_save',
                    ],
                    2 => [
                        0 => 2,
                        1 => 'fts',
                        2 => null,
                        3 => '\\' . \Sugarcrm\Sugarcrm\SearchEngine\HookHandler::class,
                        4 => 'indexBean',
                    ],
                ],
                [
                    0 => 0,
                    1 => 2,
                    2 => 1,
                ],
            ],
            // Additional short tests
            [
                [
                    0 => [4],
                    1 => [1],
                    2 => [3],
                    3 => [2],
                    4 => [5],
                ],
                [
                    0 => 1,
                    1 => 3,
                    2 => 2,
                    3 => 0,
                    4 => 4,
                ],
            ],
        ];
    }

    /**
     * @covers ::loadHookClass
     * @dataProvider dataProviderTestLoadHookClass
     *
     * @param string $class
     * @param string $file
     */
    public function testloadHookClass($class, $file, $expected, $setupFile = null, $setupContents = null)
    {
        if ($setupFile) {
            $this->createFile($setupFile, $setupContents);
        }

        $lh = new \LogicHook();
        $valid = TestReflection::callProtectedMethod($lh, 'loadHookClass', [$class, $file]);
        $this->assertSame($expected, $valid);

        if ($valid) {
            $hook = new $class();
            $this->assertInstanceOf($class, $hook);
        }
    }

    public function dataProviderTestLoadHookClass()
    {
        return [
            // Invalid class and file name - should fail
            [
                'UnknownClass',
                'include/not/valid/file.php',
                false,
                null,
                null,
            ],
            // Only supply class name namespaced - should pass
            [
                'Sugarcrm\\Sugarcrm\\inc\\Sweet',
                '',
                true,
                'include/Sweet.php',
                $this->getClassContent('Sweet', 'Sugarcrm\\Sugarcrm\\inc'),
            ],
            // Class name namespaced with bogus filename - should pass
            [
                'Sugarcrm\\Sugarcrm\\inc\\Sweeter',
                'this/will/be/ignored.php',
                true,
                'include/Sweeter.php',
                $this->getClassContent('Sweeter', 'Sugarcrm\\Sugarcrm\\inc'),
            ],
            // Only supply class name non namespaced - should fail
            [
                'BadNews',
                '',
                false,
                'BadNews.php',
                $this->getClassContent('BadNews'),
            ],
            // Only supply class name non namespaced, but in autoloader include - should pass
            [
                'GoodNews',
                '',
                true,
                'include/GoodNews.php',
                $this->getClassContent('GoodNews'),
            ],
            // Legacy loading non namespace with file location - should pass
            [
                'LegacyHookClass',
                'MyLegacyHookFile.php',
                true,
                'MyLegacyHookFile.php',
                $this->getClassContent('LegacyHookClass'),
            ],
        ];
    }

    /**
     * Generate php class code
     * @param string $class Class name
     * @param string $namespace Optional namespace
     * @return string
     */
    protected function getClassContent($class, $namespace = null, $logic = null)
    {
        return sprintf(
            '<?php %s class %s {%s}',
            $namespace ? "namespace {$namespace};" : '',
            $class,
            $logic ?: ''
        );
    }

    /**
     * Create file with given content
     * @param string $file
     * @param string $contents
     */
    protected function createFile($file, $contents)
    {
        // since we don't set the main dir, this needs to be changed to the base sugar dir so the
        // name spaces will work
        file_put_contents(SUGAR_BASE_DIR . '/' . $file, $contents);
        $this->toDelete[] = SUGAR_BASE_DIR . '/' . $file;
    }
}
