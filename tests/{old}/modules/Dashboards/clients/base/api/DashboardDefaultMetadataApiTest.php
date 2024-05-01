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

include_once 'modules/Dashboards/clients/base/api/DashboardDefaultMetadataApi.php';

/**
 * @coversDefaultClass DashboardDefaultMetadataApi
 */
class DashboardDefaultMetadataApiTest extends TestCase
{
    /**
     * @var DashboardDefaultMetadataApi
     */
    protected $api;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->api = new DashboardDefaultMetadataApi();
    }

    /**
     * Provider for testSetMetadataForTab
     */
    public function setMetadataForTabProvider(): array
    {
        return [
            [
                'metadata' => [],
                'dashboardMetadata' => null,
                'expected' => false,
            ],
            [
                'metadata' => [
                    'tabs' => [
                        [
                            'dashlets' => [
                                [
                                    'view' => [
                                        'type' => 'dashlet-console-list',
                                        'module' => 'Contacts',
                                    ],
                                    'context' => [
                                        'module' => 'Contacts',
                                    ],
                                    'width' => 12,
                                    'height' => 8,
                                    'x' => 0,
                                    'y' => 0,
                                    'autoPosition' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                'dashboardMetadata' => null,
                'expected' => false,
            ],
            [
                'metadata' => [
                    'tabs' => [
                        [
                            'dashlets' => [
                                [
                                    'view' => [
                                        'type' => 'dashlet-console-list',
                                        'module' => 'Contacts',
                                    ],
                                    'context' => [
                                        'module' => 'Contacts',
                                    ],
                                    'width' => 12,
                                    'height' => 8,
                                    'x' => 0,
                                    'y' => 0,
                                    'autoPosition' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                'dashboardMetadata' => [
                    'tabs' => [
                        [
                            'dashlets' => [
                                [
                                    'view' => [
                                        'type' => 'dashlet-console-list',
                                        'module' => 'Contacts',
                                    ],
                                    'context' => [
                                        'module' => 'Contacts',
                                    ],
                                    'width' => 8,
                                    'height' => 8,
                                    'x' => 5,
                                    'y' => 0,
                                    'autoPosition' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                'expected' => true,
            ],
        ];
    }

    /**
     * @covers ::setMetadataForTab
     * @dataProvider setMetadataForTabProvider
     * @param $metadata
     * @param $dashboardMetadata
     * @param $expected
     */
    public function testSetMetadataForTab($metadata, $dashboardMetadata, $expected)
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();
        $bean->metadata = json_encode($dashboardMetadata);

        if ($expected) {
            $bean->expects($this->once())->method('save');
        } else {
            $bean->expects($this->never())->method('save');
        }

        $actual = $this->api->setMetadataForTab($bean, 0, $metadata);
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::getFilename
     */
    public function testGetFilename()
    {
        $args = [
            'module' => 'Dashboards',
            'dashboard' => 'omnichannel',
        ];

        $expected = 'modules/Dashboards/dashboards/omnichannel/omnichannel.php';
        $actual = $this->api->getFilename($args);
        $this->assertSame($expected, $actual);
    }
}
