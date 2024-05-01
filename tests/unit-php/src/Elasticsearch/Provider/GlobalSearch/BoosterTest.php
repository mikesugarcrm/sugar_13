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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Booster
 */
class BoosterTest extends TestCase
{
    /**
     * @covers ::setWeighted
     * @covers ::getBoostValue
     * @covers ::normalizeBoost
     * @covers ::weight
     * @dataProvider dataProviderTestGetBoostedField
     */
    public function testGetBoostedField(array $weighted, array $defs, $type, $expected)
    {
        $bh = new Booster();
        $bh->setWeighted($weighted);
        $this->assertSame($expected, $bh->getBoostValue($defs, $type));
    }

    public function dataProviderTestGetBoostedField()
    {
        return [
            [
                [],
                [],
                'foo',
                1.0,
            ],
            [
                [],
                ['full_text_search' => []],
                'foo',
                1.0,
            ],
            [
                [],
                ['full_text_search' => ['boost' => 2.138]],
                'foo',
                2.14,
            ],
            [
                ['foo' => 0.5],
                ['full_text_search' => ['boost' => 2.138]],
                'foo',
                1.07,
            ],
        ];
    }
}
