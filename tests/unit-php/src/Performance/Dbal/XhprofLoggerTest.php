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

namespace Sugarcrm\SugarcrmTestsUnit\Performance\Dbal;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Performance\Dbal\XhprofLogger;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Performance\Dbal\XhprofLogger
 */
class XhprofLoggerTest extends TestCase
{
    /**
     * @return array
     * @see testStartQuery
     */
    public function startQueryDataProvider()
    {
        return [
            ['Query: SELECT \'test\' FROM DUAL'],
            [
                'Query: SELECT \'test\' FROM DUAL',
                ['some-param'],
            ],
            [
                'Query: SELECT \'test\' FROM DUAL',
                ['some-param'],
                ['param-type'],
            ],
        ];
    }

    /**
     * @dataProvider startQueryDataProvider
     * @covers ::startQuery
     *
     * @param string $sql
     * @param array $params
     * @param array $types
     */
    public function testStartQuery($sql, array $params = null, array $types = null)
    {
        $dbalXhprofLogger = new XhprofLogger(\SugarXHprof::getInstance());

        $dbalXhprofLogger->startQuery($sql, $params, $types);

        $this->assertNotEmpty($dbalXhprofLogger->currentQuery);
        $this->assertArrayHasKey('sql', $dbalXhprofLogger->currentQuery);
        $this->assertEquals($sql, $dbalXhprofLogger->currentQuery['sql']);

        $this->assertArrayHasKey('params', $dbalXhprofLogger->currentQuery);
        $this->assertEquals($params, $dbalXhprofLogger->currentQuery['params']);

        $this->assertArrayHasKey('types', $dbalXhprofLogger->currentQuery);
        $this->assertEquals($types, $dbalXhprofLogger->currentQuery['types']);
    }

    /**
     * @covers ::stopQuery
     */
    public function testStopQuery()
    {
        $sugarXhprof = $this->getMockBuilder('SugarXhprof')
            ->setMethods(['trackSQL'])
            ->getMock();

        $sugarXhprof->expects($this->once())
            ->method('trackSQL')
            ->with(
                $this->equalTo('sample-sql'),
                $this->greaterThan(0)
            )
            ->willReturn(true);

        $dbalXhprofLogger = new XhprofLogger($sugarXhprof);

        $dbalXhprofLogger->currentQuery = ['sql' => 'sample-sql', 'params' => null, 'types' => null];
        $dbalXhprofLogger->stopQuery();
    }
}
