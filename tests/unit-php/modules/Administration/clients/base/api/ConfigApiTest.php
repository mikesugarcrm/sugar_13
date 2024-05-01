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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigApiTest
 * @coversDefaultClass \ConfigApi
 */
class ConfigApiTest extends TestCase
{
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
        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->api = $this->createPartialMock(
            \ConfigApi::class,
            ['requireArgs', 'ensureAdminUser', 'getHandler']
        );
        $this->api->method('requireArgs')->willReturn(true);
        $this->api->method('ensureAdminUser')->willReturn(true);
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig(): void
    {
        $args = ['category' => 'test'];
        $handler = $this->createPartialMock(
            \ConfigApiHandler::class,
            ['getConfig']
        );
        $handler->expects($this->once())->method('getConfig')
            ->with($this->apiService, $args);
        $this->api->method('getHandler')->willReturn($handler);
        $this->api->getConfig($this->apiService, $args);
    }

    /**
     * @covers ::setConfig
     */
    public function testSetConfig(): void
    {
        $args = ['category' => 'test'];
        $handler = $this->createPartialMock(
            \ConfigApiHandler::class,
            ['setConfig']
        );
        $handler->expects($this->once())->method('setConfig')
            ->with($this->apiService, $args);
        $this->api->method('getHandler')->willReturn($handler);
        $this->api->setConfig($this->apiService, $args);
    }
}
