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

namespace Sugarcrm\SugarcrmTestsUnit\Filters\Operand;

use ServiceBase;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Operand\Operand;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Operand\Operand
 */
class OperandTest extends TestCase
{
    public function filterProvider()
    {
        return [
            '$creator: link not specified' => [
                '$creator',
                '',
            ],
            '$creator: link is _this' => [
                '$creator',
                '_this',
            ],
            '$creator: link is specified' => [
                '$creator',
                'opportunities',
            ],
            '$favorite: link not specified' => [
                '$favorite',
                '',
            ],
            '$favorite: link is _this' => [
                '$favorite',
                '_this',
            ],
            '$favorite: link is specified' => [
                '$favorite',
                'opportunities',
            ],
            '$following' => [
                '$following',
                '',
            ],
            '$owner: link not specified' => [
                '$owner',
                '',
            ],
            '$owner: link is _this' => [
                '$owner',
                '_this',
            ],
            '$owner: link is specified' => [
                '$owner',
                'opportunities',
            ],
            '$tracker' => [
                '$tracker',
                '-30 DAY',
            ],
        ];
    }

    /**
     * @covers ::apiSerialize
     * @dataProvider filterProvider
     */
    public function testApiSerialize(string $operand, $filter)
    {
        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $operand = new Operand($operand, $filter);

        $actual = $operand->apiSerialize($api);

        $this->assertSame($filter, $actual);
    }

    /**
     * @covers ::apiUnserialize
     * @dataProvider filterProvider
     */
    public function testApiUnserialize(string $operand, $filter)
    {
        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $operand = new Operand($operand, $filter);

        $actual = $operand->apiUnserialize($api);

        $this->assertSame($filter, $actual);
    }
}
