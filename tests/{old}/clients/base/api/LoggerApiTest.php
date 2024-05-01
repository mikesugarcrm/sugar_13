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
 * @group ApiTests
 */
class LoggerApiTest extends TestCase
{
    /**
     * @var LoggerApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected $serviceMock;

    protected function setUp(): void
    {
        $this->api = new LoggerApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testLogMessage()
    {
        $result = $this->api->logMessage($this->serviceMock, ['level' => 'fatal', 'message' => 'Unit Test']);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }
}
