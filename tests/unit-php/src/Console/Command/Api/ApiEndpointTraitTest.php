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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Api;

use PHPUnit\Framework\TestCase;
use PingApi;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\ApiEndpointTrait
 */
class ApiEndpointTraitTest extends TestCase
{
    /**
     * @covers ::initApi
     * @covers ::callApi
     * @requires PHP 5.4
     */
    public function testTrait()
    {
        $service = $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->getMock();

        $trait = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Console\Command\Api\ApiEndpointTrait::class)
            ->setMethods(['getService'])
            ->getMockForTrait();

        $trait->expects($this->once())
            ->method('getService')
            ->will($this->returnValue($service));

        $api = $this->createMock(PingApi::class);

        $apiCallArgs = ['foo', 'bar', ['more' => 'beer']];

        $api->expects($this->once())
            ->method('ping')
            ->with($this->equalTo($service), $this->equalTo($apiCallArgs));

        TestReflection::callProtectedMethod($trait, 'initApi', [$api]);
        TestReflection::callProtectedMethod(
            $trait,
            'callApi',
            ['ping', $apiCallArgs]
        );
    }
}
