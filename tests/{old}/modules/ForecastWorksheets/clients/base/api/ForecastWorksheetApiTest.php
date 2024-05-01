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

/**
 * Class ForecastWorksheetsApiTest
 *
 * @covers ForecastWorksheetsApi
 */
class ForecastWorksheetsApiTest extends TestCase
{
    /**
     * @covers ForecastWorksheetsApi::getClass
     */
    public function testGetClass()
    {
        $api = new ForecastWorksheetsApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getClass', [[]]);

        $this->assertInstanceOf('SugarForecasting_Individual', $klass);
    }

    /**
     * @covers ForecastWorksheetsApi::getClass
     */
    public function testGetClassReturnsCustomClass()
    {
        $file = <<<FILE
<?php
class CustomSugarForecasting_Individual extends SugarForecasting_Individual {}
FILE;
        sugar_file_put_contents('custom/include/SugarForecasting/Individual.php', $file);

        $api = new ForecastWorksheetsApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getClass', [[]]);

        $this->assertInstanceOf('CustomSugarForecasting_Individual', $klass);

        unlink('custom/include/SugarForecasting/Individual.php');
    }

    /**
     * @covers ForecastWorksheetsApi::forecastWorksheetSave
     */
    public function testForecastWorksheetSave()
    {
        SugarAutoLoader::load('include/SugarForecasting/Individual.php');
        $class = $this->getMockBuilder('SugarForecasting_Individual')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $bean = $this->getMockBuilder('Opportunity')
            ->disableOriginalConstructor()
            ->getMock();

        $class->expects($this->once())
            ->method('save')
            ->willReturn($bean);

        $api = SugarTestRestUtilities::getRestServiceMock();

        $fw_api = $this->getMOckBuilder('ForecastWorksheetsApi')
            ->setMethods(['getClass', 'formatBean'])
            ->getMock();

        $fw_api->expects($this->once())
            ->method('formatBean')
            ->with($api, [], $bean);

        $fw_api->expects($this->once())
            ->method('getClass')
            ->willReturn($class);

        $fw_api->forecastWorksheetSave($api, []);
    }
}
