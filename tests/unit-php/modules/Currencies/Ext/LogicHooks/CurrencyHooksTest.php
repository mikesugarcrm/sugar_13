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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Currencies\Ext\LogicHooks;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CurrencyHooks
 */
class CurrencyHooksTest extends TestCase
{
    protected function setUp(): void
    {
        \SugarAutoLoader::load('../../modules/Currencies/Ext/LogicHooks/CurrencyHooks.php');
        \SugarAutoLoader::load('../../modules/Currencies/Currency.php');
        \SugarAutoLoader::load('../../include/SugarQueue/SugarJobQueue.php');
        \SugarAutoLoader::load('../../modules/SchedulersJob/SchedulersJob.php');
    }

    /**
     * @dataProvider updateCurrencyConversionProvider
     * @covers ::updateCurrencyConversion
     * @param $callCount
     * @param $args
     * @param $currency_rate_old
     * @param $currency_rate_new
     */
    public function testUpdateCurrencyConversion($callCount, $args, $conversion_rate_old, $conversion_rate_new)
    {
        $currencyMock = $this->getMockBuilder('\Currency')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $currencyMock->id = 'foo';
        $currencyMock->fetched_row = ['conversion_rate' => $conversion_rate_old];
        $currencyMock->conversion_rate = $conversion_rate_new;

        $jobMock = $this->getMockBuilder('\SchedulersJob')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $jobQueueMock = $this->getMockBuilder('\SugarJobQueue')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $jobQueueMock->expects($this->exactly($callCount))
            ->method('submitJob')
            ->with($jobMock);

        $mock = $this->getMockBuilder('\CurrencyHooks')
            ->setMethods(['getSchedulersJobs', 'getSugarJobQueue'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly($callCount))
            ->method('getSchedulersJobs')
            ->will($this->returnValue($jobMock));

        $mock->expects($this->exactly($callCount))
            ->method('getSugarJobQueue')
            ->will($this->returnValue($jobQueueMock));

        $mock->updateCurrencyConversion($currencyMock, 'before_save', $args);
    }

    public function updateCurrencyConversionProvider()
    {
        return [
            [1, ['isUpdate' => true], .5, .4],
            [0, ['isUpdate' => true], .5, .5],
            [0, ['isUpdate' => false], .5, .4],
            [0, ['isUpdate' => false], .5, .5],
        ];
    }
}
