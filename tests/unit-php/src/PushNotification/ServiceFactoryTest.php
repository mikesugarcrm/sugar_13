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

namespace Sugarcrm\SugarcrmTestsUnit\PushNotification;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\PushNotification\ServiceFactory;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\PushNotification\ServiceFactory
 */
class ServiceFactoryTest extends TestCase
{
    private $sugarConfig;
    private $config;

    protected function setUp(): void
    {
        $this->sugarConfig = $GLOBALS['sugar_config'] ?? null;
        $this->config = \SugarConfig::getInstance();
    }

    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->sugarConfig;
        $this->config->clearCache();
    }

    /**
     * @covers ::getService
     * @dataProvider getServiceDataProvider
     */
    public function testGetService($config, $error, $expected)
    {
        $GLOBALS['sugar_config'] = $config;
        $this->config->clearCache();
        if ($error) {
            $loggerMock = $this->getMockBuilder('LoggerManager')
                ->disableOriginalConstructor()
                ->setMethods(['error'])
                ->getMock();
            $loggerMock->method('error')->with($error);
            Servicefactory::setLogger($loggerMock);
        }
        $this->assertEquals($expected, ServiceFactory::getService());
    }

    /**
     * Data provider.
     */
    public function getServiceDataProvider()
    {
        return [
            [
                ['push_notification' => ['enabled' => true, 'service_provider' => 'NoService']],
                "push notification: service class 'Sugarcrm\Sugarcrm\PushNotification\NoService\NoService' doesn't exist.",
                false,
            ],
            [
                ['push_notification' => ['enabled' => true]],
                '',
                // return false immediately
                false,
            ],
        ];
    }
}
