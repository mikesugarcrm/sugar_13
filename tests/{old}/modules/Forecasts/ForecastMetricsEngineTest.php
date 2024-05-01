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

use PHPUnit\Framework\TestCase;

class ForecastMetricsEngineTest extends TestCase
{
    /**
     * @covers       ForecastMetricsEngine::calculateQuota
     * @dataProvider calculateQuotaProvider
     */
    public function testCalculateQuota($expectedData)
    {
        $mock = $this->initMock('getRollupQuota');
        $mock->expects($this->any())
            ->method('getRollupQuota')
            ->willReturn([
                'currency_id' => -99,
                'amount' => 10,
            ]);

        $this->assertEquals($expectedData, $mock->calculateQuota());
    }

    public function calculateQuotaProvider()
    {
        return [
            [['type' => 'currency', 'value' => 10]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateQuotaCoverage
     * @dataProvider calculateQuotaCoverageProvider
     */
    public function testCalculateQuotaCoverage($quotaValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['quota'], ['pipeline'], ['won'], ['lost'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $quotaValue],
                ['type' => 'currency', 'value' => 4],
                ['type' => 'currency', 'value' => 6],
                ['type' => 'currency', 'value' => 8],
            );

        $data = $mock->calculateQuotaCoverage();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateQuotaCoverageProvider()
    {
        return [
            [6, ['type' => 'float', 'value' => 3]],
            [0, ['type' => 'float', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateGapQuota
     * @dataProvider calculateGapQuotaProvider
     */
    public function testCalculateGapQuota($expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['quota'], ['won'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'float', 'value' => 20],
                ['type' => 'float', 'value' => 15],
            );

        $data = $mock->calculateGapQuota();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateGapQuotaProvider()
    {
        return [
            [['type' => 'currency', 'value' => 5]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculatePctWonQuota
     * @dataProvider calculatePctWonQuotaProvider
     */
    public function testCalculatePctWonQuota($quotaValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['quota'], ['won'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $quotaValue],
                ['type' => 'currency', 'value' => 60],
            );

        $data = $mock->calculatePctWonQuota();
        $this->assertEquals($data, $expectedData);
    }

    public function calculatePctWonQuotaProvider()
    {
        return [
            [15, ['type' => 'ratio', 'value' => 4]],
            [0, ['type' => 'ratio', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateQuotaGapCoverage
     * @dataProvider calculateQuotaGapCoverageProvider
     */
    public function testCalculateQuotaGapCoverage($gapQuotaValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['gap_quota'], ['pipeline'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $gapQuotaValue],
                ['type' => 'currency', 'value' => 75],
            );

        $data = $mock->calculateQuotaGapCoverage();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateQuotaGapCoverageProvider()
    {
        return [
            [25, ['type' => 'float', 'value' => 3]],
            [0, ['type' => 'float', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateCommitmentCoverage
     * @dataProvider calculateCommitmentCoverageProvider
     */
    public function testCalculateCommitmentCoverage($commitmentValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['commitment'], ['pipeline'], ['won'], ['lost'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $commitmentValue],
                ['type' => 'currency', 'value' => 10],
                ['type' => 'currency', 'value' => 20],
                ['type' => 'currency', 'value' => 30],
            );

        $data = $mock->calculateCommitmentCoverage();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateCommitmentCoverageProvider()
    {
        return [
            [5, ['type' => 'float', 'value' => 12]],
            [0, ['type' => 'float', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateGapCommitment
     * @dataProvider calculateGapCommitmentProvider
     */
    public function testCalculateGapCommitment($expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['commitment'], ['won'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'float', 'value' => 20],
                ['type' => 'float', 'value' => 8],
            );

        $data = $mock->calculateGapCommitment();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateGapCommitmentProvider()
    {
        return [
            [['type' => 'currency', 'value' => 12]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateCommitmentGapCoverage
     * @dataProvider calculateCommitmentGapCoverageProvider
     */
    public function testCalculateCommitmentGapCoverage($gapCommitmentValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['gap_commitment'], ['pipeline'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $gapCommitmentValue],
                ['type' => 'currency', 'value' => 100],
            );

        $data = $mock->calculateCommitmentGapCoverage();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateCommitmentGapCoverageProvider()
    {
        return [
            [5, ['type' => 'float', 'value' => 20]],
            [0, ['type' => 'float', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculatePctWonCommitment
     * @dataProvider calculatePctWonCommitmentProvider
     */
    public function testCalculatePctWonCommitment($commitmentValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['commitment'], ['won'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $commitmentValue],
                ['type' => 'currency', 'value' => 90],
            );

        $data = $mock->calculatePctWonCommitment();
        $this->assertEquals($data, $expectedData);
    }

    public function calculatePctWonCommitmentProvider()
    {
        return [
            [3, ['type' => 'ratio', 'value' => 30]],
            [0, ['type' => 'ratio', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateForecastCoverage
     * @dataProvider calculateForecastCoverageProvider
     */
    public function testCalculateForecastCoverage($forecastValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['forecast_list'], ['pipeline'], ['won'], ['lost'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $forecastValue],
                ['type' => 'currency', 'value' => 10],
                ['type' => 'currency', 'value' => 20],
                ['type' => 'currency', 'value' => 30],
            );

        $data = $mock->calculateForecastCoverage();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateForecastCoverageProvider()
    {
        return [
            [15, ['type' => 'float', 'value' => 4]],
            [0, ['type' => 'float', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::CalculateGapForecast
     * @dataProvider calculateGapForecastProvider
     */
    public function testCalculateGapForecast($expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['forecast_list'], ['won'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'float', 'value' => 18],
                ['type' => 'float', 'value' => 6],
            );

        $data = $mock->calculateGapForecast();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateGapForecastProvider()
    {
        return [
            [['type' => 'currency', 'value' => 12]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculateForecastGapCoverage
     * @dataProvider calculateForecastGapCoverageProvider
     */
    public function testCalculateForecastGapCoverage($gapForecastValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['gap_forecast'], ['pipeline'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $gapForecastValue],
                ['type' => 'currency', 'value' => 50],
            );

        $data = $mock->calculateForecastGapCoverage();
        $this->assertEquals($data, $expectedData);
    }

    public function calculateForecastGapCoverageProvider()
    {
        return [
            [5, ['type' => 'float', 'value' => 10]],
            [0, ['type' => 'float', 'value' => 0]],
        ];
    }

    /**
     * @covers       ForecastMetricsEngine::calculatePctWonForecast
     * @dataProvider calculatePctWonForecastProvider
     */
    public function testCalculatePctWonForecast($forecastValue, $expectedData)
    {
        $mock = $this->initMock('getMetric');
        $mock->expects($this->any())
            ->method('getMetric')
            ->withConsecutive(['forecast_list'], ['won'])
            ->willReturnOnConsecutiveCalls(
                ['type' => 'currency', 'value' => $forecastValue],
                ['type' => 'currency', 'value' => 60],
            );

        $data = $mock->calculatePctWonForecast();
        $this->assertEquals($data, $expectedData);
    }

    public function calculatePctWonForecastProvider()
    {
        return [
            [4, ['type' => 'ratio', 'value' => 15]],
            [0, ['type' => 'ratio', 'value' => 0]],
        ];
    }

    public function initMock($methods)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        return $this->getMockBuilder('ForecastMetricsEngine')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
