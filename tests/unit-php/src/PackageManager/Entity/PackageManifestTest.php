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

namespace Sugarcrm\SugarcrmTestsUnit\PackageManager\Entity;

require_once 'include/SugarCache/SugarCache.php';

use Sugarcrm\Sugarcrm\PackageManager\Entity\PackageManifest;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\PackageManager\Exception\PackageManifestException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\PackageManager\Entity\PackageManifest
 */
class PackageManifestTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['log'] = $this->getMockBuilder(\LoggerManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
    }

    public function providerTestConstructException()
    {
        return [
            'no id and name' => [[], [], [],],
            'no version' => [['name' => 'test',], [], [],],
            'no type' => [['name' => 'test', 'version' => '100',], [], [],],
            'wrong type' => [['name' => 'test', 'version' => '100', 'type' => 'wrong',], [], [],],
            'no acceptable_sugar_versions' => [
                [
                    'name' => 'test',
                    'version' => '100',
                    'type' => PackageManifest::PACKAGE_TYPE_MODULE,
                ],
                [],
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerTestConstructException
     * @covers ::__construct
     * @param array $manifest
     * @param array $installDefs
     * @param array $upgradeManifest
     */
    public function testConstructException(array $manifest, array $installDefs, array $upgradeManifest)
    {
        $this->expectException(PackageManifestException::class);
        new PackageManifest($manifest, $installDefs, $upgradeManifest);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $data = [
            'version' => '100',
            'type' => PackageManifest::PACKAGE_TYPE_MODULE,
            'acceptable_sugar_versions' => ['10.1'],
        ];
        $manifest = new PackageManifest($data, ['id' => 'id_name'], []);

        $this->assertEquals('id_name', $manifest->getPackageIdName());
        $this->assertEquals('id_name', $manifest->getPackageName());
        $this->assertEquals(['regex_matches' => ['^10\.1\.([0-9]+)']], $manifest->getAcceptableSugarVersions());
    }

    /**
     * @covers ::__construct
     */
    public function testConstructBuildInVersion()
    {
        $data = [
            'name' => 'name_from_manifest',
            'version' => '100',
            'type' => PackageManifest::PACKAGE_TYPE_MODULE,
            'built_in_version' => '9.0.1',
        ];
        $manifest = new PackageManifest($data, [], []);

        $this->assertEquals('name_from_manifest', $manifest->getPackageIdName());
        $this->assertEquals('name_from_manifest', $manifest->getPackageName());
        $this->assertEquals(['regex_matches' => ['^9\.([0-9]+)\.([0-9]+)']], $manifest->getAcceptableSugarVersions());
    }

    public function providerTestConvertOldBoolValue()
    {
        return [
            'string' => ['false', false],
            'bool' => [true, true],
            'int' => [0, false],
        ];
    }

    /**
     * @dataProvider providerTestConvertOldBoolValue
     * @covers ::shouldTablesBeRemoved
     * @covers ::convertOldBoolValue
     */
    public function testConvertOldBoolValue($removeTables, $result)
    {
        $data = [
            'name' => 'name',
            'version' => '100',
            'type' => PackageManifest::PACKAGE_TYPE_MODULE,
            'built_in_version' => '9.0.1',
            'remove_tables' => $removeTables,
        ];
        $manifest = new PackageManifest($data, [], []);
        $this->assertEquals($result, $manifest->shouldTablesBeRemoved());
    }
}
