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
 * @coversDefaultClass PMSEProjectApi
 */
class PMSEProjectApiTest extends TestCase
{
    public function providerValidateCrmData()
    {
        return [
            [
                'existing_key',
                [
                    'result' => true,
                ],
            ],
            [
                'non_existing_key',
                [
                    'result' => false,
                ],
            ],
        ];
    }

    /**
     * @covers ::validateCrmData
     * @dataProvider providerValidateCrmData
     */
    public function testValidateCrmData($key, $expected)
    {
        $mockRecordsResults = [
            'result' => [
                [
                    'value' => 'existing_key',
                    'text' => 'Existing Record',
                ],
                [
                    'value' => 'another_existing_key',
                    'text' => 'Another Existing Record',
                ],
            ],
        ];

        $mockServiceBase = $this->getMockBuilder('ServiceBase')
            ->disableOriginalConstructor()
            ->getMock();

        $mockApi = $this->getMockBuilder('PMSEProjectApi')
            ->disableOriginalConstructor()
            ->onlyMethods(['getCrmData'])
            ->getMock();
        $mockApi->expects($this->once())
            ->method('getCrmData')
            ->willReturn($mockRecordsResults);

        $args = [
            'module' => 'pmse_Project',
            'data' => 'fake_data_type',
            'key' => $key,
        ];
        $this->assertEquals($expected, $mockApi->validateCrmData($mockServiceBase, $args));
    }
}
