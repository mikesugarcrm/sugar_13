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
class HintApiTest extends TestCase
{
    /**
     * @var \RestService|mixed
     */
    public $serviceMock;
    /** @var HintApi */
    private $hintApi;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->hintApi = new HintApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function testReadConfig()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);

        $this->hintApi->readConfig($this->serviceMock, []);
    }

    public function testGetHintLicenseType()
    {
        $res = $this->hintApi->getHintLicenseType($this->serviceMock, []);

        $this->assertArrayHasKey('isHintUser', $res);
    }

    public function testReadEnrichFieldConfig()
    {
        $res = $this->hintApi->readEnrichFieldConfig($this->serviceMock, []);
        $this->assertArrayHasKey('response', $res);
    }

    public function testGetParams()
    {
        $res = $this->hintApi->getParams($this->serviceMock, []);
        $this->assertArrayHasKey('serviceUrl', $res);
        $this->assertArrayHasKey('instanceId', $res);
        $this->assertArrayHasKey('analyticsUserId', $res);
        $this->assertArrayHasKey('notificationsServiceUrl', $res);
    }

    public function testUpdateConfigNotificationsHint()
    {
        $args = [
            'disableNotifications' => false,
        ];
        $hintApiMock = $this->getMockBuilder('HintApi')
            ->onlyMethods(['getConfigNotificationsHint'])
            ->getMock();


        $hintApiMock->method('getConfigNotificationsHint')
            ->willReturn(null);

        $res = $hintApiMock->updateConfigNotificationsHint($this->serviceMock, $args);

        $this->assertEquals('200', $res['status']);
    }
}
