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

namespace Sugarcrm\SugarcrmTestsUnit\Util;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\MemoryUsageRecorderTrait;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Util\MemoryUsageRecorderTrait
 */
class MemoryUsageRecorderTraitTest extends TestCase
{
    use MemoryUsageRecorderTrait;

    private static $memUse;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$memUse = ini_get('memory_limit');
    }

    public static function tearDownAfterClass(): void
    {
        ini_set('memory_limit', static::$memUse);
        parent::tearDownAfterClass();
    }

    /**
     * @covers ::checkMemoryUsageVsLimit
     * @dataProvider providerTestCheckMemoryUsageVsLimit
     */
    public function testCheckMemoryUsageVsLimit($limit, $expected)
    {
        ini_set('memory_limit', $limit());
        $this->assertEquals(round($expected, -1), round($this->checkMemoryUsageVsLimit(), -1));
    }

    public function providerTestCheckMemoryUsageVsLimit()
    {
        return [
            [function () {
                return -1;
            }, 0],
            [function () {
                return intval($this->getMemoryUsage() * 10) . 'K';
            }, 10],
            [function () {
                return intval($this->getMemoryUsage() * 2) . 'K';
            }, 50],
            [function () {
                return intval($this->getMemoryUsage() * 1024 * 2);
            }, 100],
        ];
    }
}
