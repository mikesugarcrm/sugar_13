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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Reports\clients\base\api;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ReportsApi
 */
class ReportsApiTest extends TestCase
{
    /**
     * @covers ::getPagination
     * @dataProvider providerTestGetPagination
     */
    public function testGetPagination($args, $mockLimit, $expected)
    {
        $mockApi = $this->getReportsApiMock(['checkMaxListLimit']);
        $mockApi->method('checkMaxListLimit')->willReturn($mockLimit);
        $result = TestReflection::callProtectedMethod($mockApi, 'getPagination', [null, $args]);
        $this->assertSame($result, $expected);
    }

    public function providerTestGetPagination()
    {
        return [
            [['offset' => 0, 'max_num' => 20], 20, [0, 20]],
            [['offset' => 20, 'max_num' => 20], 20, [20, 20]],
            [['max_num' => 20], 20, [0, 20]],
            [['offset' => 0], -1, [0, -1]],
            [['offset' => -1], -1, [0, -1]],
            [['offset' => 0, 'max_num' => -1], -1, [0, -1]],
        ];
    }

    /**
     * @param null|array $methods
     * @return \ReportsApi
     */
    protected function getReportsApiMock($methods = null)
    {
        return $this->getMockBuilder('ReportsApi')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
