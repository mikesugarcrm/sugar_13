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

namespace Sugarcrm\SugarcrmTestsUnit\Dbal\Logging;

use Monolog\Formatter\FormatterInterface;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Dbal\Logging\Formatter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dbal\Logging\DebugLogger
 */
class FormatterTest extends TestCase
{
    public function startQueryDataProvider()
    {
        return [
            ['SELECT \'test\' FROM DUAL'],
            [
                'SELECT \'test\' FROM DUAL'
                . PHP_EOL . 'Params: ["some-param"]',
                ['some-param'],
            ],
            [
                'SELECT \'test\' FROM DUAL'
                . PHP_EOL . 'Params: ["some-param"]'
                . PHP_EOL . 'Types: ["param-type"]',
                ['some-param'],
                ['param-type'],
            ],
        ];
    }

    /**
     * @dataProvider startQueryDataProvider
     * @covers ::startQuery
     */
    public function testFormat($expectedMessage, array $params = null, array $types = null)
    {
        $mock = $this->createMock(FormatterInterface::class);
        $mock->expects($this->once())
            ->method('format')
            ->with($this->containsEqual($expectedMessage));

        $formatter = new Formatter($mock);
        $formatter->format([
            'message' => 'SELECT \'test\' FROM DUAL',
            'context' => [
                'params' => $params,
                'types' => $types,
            ],
        ]);
    }
}
