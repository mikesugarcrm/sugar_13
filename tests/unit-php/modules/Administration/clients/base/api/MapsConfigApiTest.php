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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api;

use BeanFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use MapsConfigApi;
use SugarTestHelper;

/**
 * Class MapsConfigApiTest
 * @coversDefaultClass \MapsConfigApi
 */
class MapsConfigApiTest extends TestCase
{
    public const ORIGINAL_DEFS_PATH = 'modules/Accounts/clients/base/filters/default/default.php';
    public const TEST_DEFS_PATH = 'modules/Accounts/clients/base/filters/default/mapsTest.php';
    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @var \AdministrationApi|MockObject
     */
    private $api;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        copy(
            self::ORIGINAL_DEFS_PATH,
            self::TEST_DEFS_PATH
        );

        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->api = $this->createPartialMock(
            MapsConfigApi::class,
            [
                'requireArgs',
                'ensureAdminUser',
                'getHandler',
                'getFilterMetadataFilename',
                'getConfig',
                'refreshCacheSections',
            ]
        );
        $this->api->method('requireArgs')->willReturn(true);
        $this->api->method('ensureAdminUser')->willReturn(true);
        $this->api->method('getConfig')->willReturn(['maps_enabled_modules' => []]);
        $this->api->method('getFilterMetadataFilename')->willReturn(self::TEST_DEFS_PATH);
        $this->api->method('refreshCacheSections')->willReturn(true);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unlink(self::TEST_DEFS_PATH);
        parent::tearDown();
    }

    /**
     * @covers ::setConfig
     */
    public function testSetConfig(): void
    {
        $args = ['category' => 'test', 'maps_enabled_modules' => ['Accounts']];

        $handler = $this->createPartialMock(
            \ConfigApiHandler::class,
            ['setConfig']
        );

        $handler->expects($this->any())->method('setConfig')
            ->with($this->apiService, $args);
        $this->api->method('getHandler')->willReturn($handler);

        $viewdefs = [];

        try {
            $this->api->setConfig($this->apiService, $args);

            if (hasMapsLicense()) {
                require self::TEST_DEFS_PATH;

                $this->assertArrayHasKey(
                    '$distance',
                    $viewdefs['Accounts']['base']['filter']['default']['fields'],
                    'Filter defs does not contain the distance field'
                );
            }
        } catch (\Exception $e) {
            $mapsLicenseLabel = translate('LBL_MAPS_NO_LICENSE_ACCESS');

            $this->assertEquals($mapsLicenseLabel, $e->getMessage());
        }
    }
}
