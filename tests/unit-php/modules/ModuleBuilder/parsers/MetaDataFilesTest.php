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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ModuleBuilder\parsers;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass MetaDataFiles
 */
class MetaDataFilesTest extends TestCase
{
    protected $modName = 'MetaDataFilesTestModule';

    public function displayParamsProvider()
    {
        return [[[
            'name' => 'test1',
            'meta' => [
                'panels' => [
                    [
                        'fields' => [
                            //Field with no vardef
                            'noVardef',
                            //Named field with display params in vardef
                            ['name' => 'namedField'],
                            //Field with display params in viewdef only
                            [
                                'name' => 'field3',
                                'type' => 'aType',
                                'displayParams' => [
                                    'type' => 'bType',
                                    'more' => true,
                                ],
                            ],
                            //Field with display params in both locations
                            [
                                'name' => 'field4',
                                'type' => 'aType',
                                'displayParams' => [
                                    'type' => 'bType',
                                    'more' => false,
                                ],
                            ],
                            //Field with no vardef but a more complex viewdef
                            [
                                'name' => 'field1',
                                'type' => 'newType',
                            ],
                        ],
                    ],
                ],
            ],
            'field_defs' => [
                'namedField' => [
                    'name' => 'namedField',
                    'displayParams' => ['type' => 'fromVDP'],
                ],
                'field3' => ['name' => 'field3'],
                'field4' => [
                    'name' => 'field4',
                    'displayParams' => [
                        'type' => 'cType',
                        'more' => true,
                        'vd_only' => true,
                    ],
                ],
            ],
            'expected' => [
                'test1' => [
                    'meta' => [
                        'panels' => [
                            [
                                'fields' => [
                                    //Field with no vardef
                                    [
                                        'name' => 'noVardef',
                                    ],
                                    //Named field with display params in vardef
                                    [
                                        'name' => 'namedField',
                                        'type' => 'fromVDP',
                                    ],
                                    //Field with display params in viewdef only
                                    [
                                        'name' => 'field3',
                                        'type' => 'aType',
                                        'more' => true,
                                    ],
                                    //Field with display params in both locations
                                    [
                                        'name' => 'field4',
                                        'type' => 'aType',
                                        'more' => false,
                                        'vd_only' => true,
                                    ],
                                    //Field with no vardef but a more complex viewdef
                                    [
                                        'name' => 'field1',
                                        'type' => 'newType',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        \BeanFactory::setBeanClass($this->modName, MockBean::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        MetaDataFilesTestMock::reset();
        MockBean::setFieldDef([]);
        \BeanFactory::unsetBeanClass($this->modName);
    }

    /**
     * @param array $entry
     *
     * @dataProvider displayParamsProvider
     * @covers ::buildModuleClientCache
     */
    public function testbuildModuleClientCacheMergesDisplayParams(array $entry)
    {
        MockBean::setFieldDef($entry['field_defs']);
        MetaDataFilesTestMock::$files = ['test.php'];
        MetaDataFilesTestMock::$contents = [
            $entry['name'] => ['meta' => $entry['meta']],
        ];

        $result = MetaDataFilesTestMock::buildModuleClientCache(
            ['base'],
            'view',
            $this->modName,
            null,
            true
        );

        $this->assertEquals($entry['expected'], $result);
    }

    /**
     * @covers ::getNames
     */
    public function testNamesIncludesRecordDashlet()
    {
        $names = MetaDataFilesTestMock::getNames();
        $this->assertEquals('recorddashlet', $names[MB_RECORDDASHLETVIEW]);
    }
}

class MetaDataFilesTestMock extends \MetaDataFiles
{
    public static $files;

    public static $contents;

    public static function getClientFiles(
        $platforms,
        $type,
        $module = null,
        \MetaDataContextInterface $context = null,
        $bean = null
    ) {

        if (!empty(self::$files)) {
            return self::$files;
        }

        return \MetaDataFiles::getClientFiles($platforms, $type, $module, $context, $bean);
    }

    public static function getClientFileContents($fileList, $type, $module = '', $bean = null, $platform = 'base')
    {
        if (!empty(self::$contents)) {
            return self::$contents;
        }

        return \MetaDataFiles::getClientFileContents($fileList, $type, $module, $bean);
    }

    public static function reset()
    {
        self::$files = self::$contents = null;
    }
}

class MockBean extends \SugarBean
{
    private static $fieldDefs = [];

    public static function setFieldDef(array $defs)
    {
        static::$fieldDefs = $defs;
    }

    public function __construct()
    {
        $this->field_defs = static::$fieldDefs;
    }
}
