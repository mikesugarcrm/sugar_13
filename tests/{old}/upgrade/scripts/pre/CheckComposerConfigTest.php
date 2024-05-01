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

use PHPUnit\Framework\MockObject\MockObject;

require_once 'upgrade/scripts/pre/CheckComposerConfig.php';

/**
 * CheckComposerConfig pre script test suite
 */
class SugarUpgradeCheckComposerConfigTest extends UpgradeTestCase
{
    /**
     * @var string Default context source_dir
     */
    protected $sourceDir;

    /**
     * @var string Default context new_source_dir
     */
    protected $newSourceDir;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable logging
        unset($this->upgrader->context['log']);

        // Default context
        $this->sourceDir = $this->upgrader->context['source_dir'] = sugar_cached('composerupgrade/src');
        $this->newSourceDir = $this->upgrader->context['new_source_dir'] = sugar_cached('composerupgrade/newsrc');
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::initialize
     */
    public function testInitialize()
    {
        $sut = $this->getMockSut();

        $sut->upgrader->context = [];
        $this->assertFalse(SugarTestReflection::callProtectedMethod($sut, 'initialize'));

        $sut->upgrader->context['source_dir'] = 'src';
        $sut->upgrader->context['new_source_dir'] = 'new';
        $this->assertTrue(SugarTestReflection::callProtectedMethod($sut, 'initialize'));
        $this->assertNotEmpty(SugarTestReflection::getProtectedValue($sut, 'jsonFile'));
        $this->assertNotEmpty(SugarTestReflection::getProtectedValue($sut, 'lockFile'));
        $this->assertNotEmpty(SugarTestReflection::getProtectedValue($sut, 'newJsonFile'));
    }

    /**
     * @group unit
     * @covers       SugarUpgradeCheckComposerConfig::validateGenericSettings
     *
     * @dataProvider dataProviderTestValidateGenericSettings
     * @param array $target Target definition
     * @param array $config Composer configuration
     * @param boolean $expected Valid or not
     */
    public function testValidateGenericSettings(array $target, array $config, $expected)
    {
        $result = SugarTestReflection::callProtectedMethod(
            $this->getMockSut(),
            'validateGenericSettings',
            [$target, $config]
        );

        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestValidateGenericSettings()
    {
        return [
            [
                [
                    'generic' => [],
                ],
                [],
                true,
            ],
            [
                [
                    'generic' => [
                        'name' => 'foo/bar',
                        'description' => 'beer',
                        'config' => [
                            'sweet' => 'sugar',
                        ],
                    ],
                ],
                [
                    'name' => 'foo/bar',
                    'description' => 'beer',
                    'config' => [
                        'sweet' => 'sugar',
                    ],
                ],
                true,
            ],
            [
                [
                    'generic' => [
                        'name' => 'foo/bar',
                        'description' => 'beer',
                    ],
                ],
                [],
                false,
            ],
            [
                [
                    'generic' => [
                        'name' => 'foo/bar',
                        'description' => 'beer',
                    ],
                ],
                [
                    'name' => 'foo/bar',
                    'description' => 'coke',
                ],
                false,
            ],
        ];
    }

    /**
     * @group unit
     * @covers       SugarUpgradeCheckComposerConfig::createProposal
     *
     * @dataProvider dataProviderTestCreateProposal
     * @param array $config Current composer config
     * @param array $generic Generic config settings
     * @param array $pack Missing packages
     * @param array $expected
     */
    public function testCreateProposal(array $config, array $generic, array $pack, array $expected)
    {
        $sut = $this->getMockSut(['saveToFile']);
        SugarTestReflection::callProtectedMethod($sut, 'initialize');

        $expectedFile = sprintf(
            '%s/%s.proposal',
            $this->newSourceDir,
            SugarUpgradeCheckComposerConfig::COMPOSER_JSON
        );

        $sut->expects($this->once())
            ->method('saveToFile')
            ->with($this->equalTo($expectedFile), $this->equalTo($expected));

        SugarTestReflection::callProtectedMethod(
            $sut,
            'createProposal',
            [$config, $generic, $pack]
        );
    }

    public function dataProviderTestCreateProposal()
    {
        return [
            // Test generic settings override
            [
                [
                    'name' => 'foo',
                    'config' => 'bar',
                ],
                [
                    'name' => 'new',
                ],
                [],
                [
                    'name' => 'new',
                    'config' => 'bar',
                ],
            ],
            // Test missing module
            [
                [
                    'name' => 'foo',
                    'config' => 'bar',
                ],
                [],
                [
                    'sugarcrm/modulex' => '1.2.3',
                    'sugarcrm/moduley' => 'v1.0',
                ],
                [
                    'name' => 'foo',
                    'config' => 'bar',
                    'require' => [
                        'sugarcrm/modulex' => '1.2.3',
                        'sugarcrm/moduley' => 'v1.0',
                    ],
                ],
            ],
            // Test mix
            [
                [
                    'name' => 'foo',
                    'config' => 'bar',
                    'require' => [
                        'existing/lib' => '4.5.6',
                    ],
                ],
                [
                    'name' => 'new',
                    'config' => [
                        'config1' => true,
                        'config2' => false,
                        'config3' => 'ok',
                    ],
                ],
                [
                    'sugarcrm/modulex' => '1.2.3',
                    'sugarcrm/moduley' => 'v1.0',
                ],
                [
                    'name' => 'new',
                    'config' => [
                        'config1' => true,
                        'config2' => false,
                        'config3' => 'ok',
                    ],
                    'require' => [
                        'existing/lib' => '4.5.6',
                        'sugarcrm/modulex' => '1.2.3',
                        'sugarcrm/moduley' => 'v1.0',
                    ],
                ],
            ],
        ];
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::useCustomComposerFiles
     */
    public function testUseCustomComposerFiles()
    {
        $sut = $this->getMockSut(['copy']);
        $this->assertArrayNotHasKey('composer_custom', ($sut->upgrader->state));

        $sut->expects($this->exactly(2))
            ->method('copy')
            ->withConsecutive(
                [
                    'composer.json',
                    'composer.json.valid',
                ],
                [
                    'composer.lock',
                    'composer.lock.valid',
                ]
            );

        SugarTestReflection::callProtectedMethod($sut, 'useCustomComposerFiles', [
            [
                'composer.json',
                'composer.lock',
            ],
        ]);

        $this->assertArrayHasKey('composer_custom', $sut->upgrader->state);
        $this->assertSame([
            'composer.json',
            'composer.lock',
        ], $sut->upgrader->state['composer_custom']);
    }

    /**
     * @group unit
     * @covers       SugarUpgradeCheckComposerConfig::isPlatformPackage
     *
     * @dataProvider dataProviderTestIsPlatformPackage
     * @param array $tests List of tests and assertions
     */
    public function testIsPlatformPackage(array $tests)
    {
        $sut = $this->getMockSut();

        foreach ($tests as $test => $expected) {
            $this->assertSame(
                $expected,
                SugarTestReflection::callProtectedMethod($sut, 'isPlatformPackage', [$test])
            );
        }
    }

    public function dataProviderTestIsPlatformPackage()
    {
        return [
            [
                [
                    'php' => true,
                    'ext-apc' => true,
                    'lib-gd' => true,
                    'sugarcrm/sugarcrm' => false,
                    'monolog/monolog' => false,
                ],
            ],
        ];
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::isPackageAvailable
     */
    public function testIsPackageAvailable()
    {
        $sut = $this->getMockSut();
        $lock = ['sugarcrm/sugarcrm' => '7.6.0.1'];

        $this->assertTrue(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            ['sugarcrm/sugarcrm', '7.6.0.1', $lock]
        ));

        $this->assertTrue(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            ['php', '5.5.0', $lock]
        ));


        $this->assertFalse(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            ['sugarcrm/sugarcrm', '7.6.0.2', $lock]
        ));

        $this->assertFalse(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            ['foo/bar', '7.6.0.1', $lock]
        ));
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::getMissingPackages
     */
    public function testGetMissingPackages()
    {
        $target = [
            'foo' => 'bar',
        ];

        $sut = $this->getMockSut(['isPackageAvailable']);
        $sut->expects($this->exactly(count($target)))
            ->method('isPackageAvailable');

        SugarTestReflection::callProtectedMethod($sut, 'getMissingPackages', [$target, []]);
    }

    /**
     * Get mock for subject under test
     * @param null|array $method
     * @param array $context Additional context settings
     * @return SugarUpgradeCheckComposerConfig|MockObject
     */
    protected function getMockSut($method = null, array $context = [])
    {
        foreach ($context as $k => $v) {
            $this->upgrader->context[$k] = $v;
        }

        return $this->getMockBuilder('SugarUpgradeCheckComposerConfig')
            ->setConstructorArgs([$this->upgrader])
            ->setMethods($method)
            ->getMock();
    }

    public function testIsStockComposer()
    {
        $isStockComposer = $this->isStockComposer('the-hash', 'the-hash');
        $this->assertTrue($isStockComposer);
    }

    /**
     * @dataProvider isNotStockComposerProvider
     */
    public function testIsNotStockComposer($actualHash, $stockHash)
    {
        $isStockComposer = $this->isStockComposer($actualHash, $stockHash);
        $this->assertFalse($isStockComposer);
    }

    public static function isNotStockComposerProvider()
    {
        return [
            'empty-actual-hash' => [null, null],
            'empty-stock-hash' => [null, 'X'],
            'actual-stock-mismatch' => ['Y', 'X'],
        ];
    }

    private function isStockComposer($stockHash, $actualHash)
    {
        $sut = $this->getMockSut(['loadLock', 'getActualHash', 'getStockHash']);

        $sut->expects($this->any())
            ->method('getActualHash')
            ->willReturn($actualHash);
        $sut->expects($this->any())
            ->method('getStockHash')
            ->willReturn($stockHash);
        return SugarTestReflection::callProtectedMethod($sut, 'isStockComposer');
    }

    public function testStockFilesAreRecognized()
    {
        $sut = $this->getMockSut(null, [
            'source_dir' => SUGAR_BASE_DIR,
        ]);

        $this->assertTrue(
            SugarTestReflection::callProtectedMethod($sut, 'initialize')
        );

        $this->assertTrue(
            SugarTestReflection::callProtectedMethod($sut, 'isStockComposer')
        );
    }
}
