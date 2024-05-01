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

namespace Sugarcrm\SugarcrmTestsUnit\inc\MetaDataManager;

use PHPUnit\Framework\TestCase;

/**
 * Class MetaDataManagerKioskTest
 *
 * @coversDefaultClass \MetaDataManagerKiosk
 */
class MetaDataManagerKioskTest extends TestCase
{
    /**
     * @covers ::getMetadata
     * @dataProvider getMetadataProvider
     */
    public function testGetMetadata($isServe, $settings, $expected)
    {
        $adminMock = $this->createPartialMock(
            '\Administration',
            ['retrieveSettings']
        );
        $adminMock->method('retrieveSettings')->willReturn($adminMock);
        $adminMock->settings = $settings;

        $metadataManagerKioskMock = $this->createPartialMock(
            '\MetaDataManagerKiosk',
            ['getAdministration']
        );
        $metadataManagerKioskMock->method('getAdministration')->willReturn($adminMock);

        $data = $metadataManagerKioskMock->getMetadata();
        $this->assertEquals($expected, $data['config']);
    }

    /**
     * DataProvider for testGetMetadata
     */
    public function getMetadataProvider()
    {
        return [
            [
                true,
                [
                    'someKey' => 'someValue',
                    'aws_connect_api_gateway_url' => 'https://example.my.connect.aws/ccp-v2',
                    'aws_connect_region' => 'eu-west-2',
                ],
                [
                    'awsConnectApiGatewayUrl' => 'https://example.my.connect.aws/ccp-v2',
                    'awsConnectRegion' => 'eu-west-2',
                ],
            ],
            [
                false,
                [
                    'someKey' => 'someValue',
                ],
                [],
            ],
        ];
    }
}
