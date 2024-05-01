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

namespace Sugarcrm\SugarcrmTestsUnit\PackageManager;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\PackageManager\VersionComparator;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\PackageManager\VersionComparator
 */
class VersionComparatorTest extends TestCase
{
    public static function compareDataProvider(): array
    {
        return [
            ['1', '1', '>=', true],
            ['4.6', 'v4.6', '>=', true],
            ['4.2', '4.10', '<', true],
            ['4.7', 'v4.6', '>=', true],
            ['v4.7', '4.6', '>=', true],
            ['4.5', 'v4.6', '>=', false],
            ['v4.5', '4.6', '>=', false],
            ['1.2.3.4.5', '1.2', '>=', true],
            ['4.6-beta', '4.6', '>=', false],
            ['1.0', '1.0.0', '<', true], // looks like uncommon case, just document behavior of native version_compare
        ];
    }

    /**
     * @covers ::equalTo
     */
    public function testEqualTo()
    {
        $this->assertTrue(VersionComparator::equalTo('v1.0', '1.0'));
    }

    /**
     * @covers ::notEqualTo
     */
    public function testsNotEqualTo()
    {
        $this->assertTrue(VersionComparator::notEqualTo('1.1', '1.10'));
    }

    /**
     * @covers ::greaterThan
     */
    public function testGreaterThan()
    {
        $this->assertTrue(VersionComparator::greaterThan('1.10', '1.2'));
        $this->assertFalse(VersionComparator::greaterThan('1.10', '1.10'));
    }

    /**
     * @covers ::greaterThanOrEqualTo
     */
    public function testGreaterThanOrEqualTo()
    {
        $this->assertTrue(VersionComparator::greaterThanOrEqualTo('1.10', '1.2'));
    }

    /**
     * @covers ::lessThan
     */
    public function testsLessThan()
    {
        $this->assertTrue(VersionComparator::lessThan('2.1', '2.1.2'));
        $this->assertFalse(VersionComparator::lessThan('2.1', '2.1'));
    }

    /**
     * @covers ::lessThanOrEqualTo
     */
    public function testLessThanOrEqualTo()
    {
        $this->assertTrue(VersionComparator::lessThanOrEqualTo('2.1', '2.1.2'));
        $this->assertTrue(VersionComparator::lessThanOrEqualTo('2.1', '2.1'));
    }

    /**
     * @covers ::compare
     * @dataProvider compareDataProvider
     */
    public function testCompare(string $version1, string $version2, string $operator, bool $result)
    {
        $this->assertEquals($result, VersionComparator::compare($version1, $operator, $version2));
    }
}
