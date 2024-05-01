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
 * Class SugarHeartbeatClientTest
 *
 * @group BR-1722
 */
class SugarHeartbeatClientTest extends TestCase
{
    /**
     * @covers SugarHeartbeatClient::sugarHome
     * @covers SugarHeartbeatClient::encode
     * @group unit
     */
    public function testSugarHome()
    {
        $result = [
            'key' => '12345',
            'data' => base64_encode(serialize(['foo' => 'bar'])),
        ];
        $client = $this->getMockBuilder('SugarHeartbeatClient')
            ->disableOriginalConstructor()
            ->setMethods(['__soapCall'])
            ->getMock();
        $client->expects($this->once())
            ->method('__soapCall')
            ->with($this->equalTo('sugarHome'), $this->equalTo($result))
            ->willReturn('');
        $client->sugarHome('12345', ['foo' => 'bar']);
    }

    /**
     * @covers SugarHeartbeatClient::sugarPing
     * @group unit
     */
    public function testSugarPing()
    {
        $client = $this->getMockBuilder('SugarHeartbeatClient')
            ->disableOriginalConstructor()
            ->setMethods(['__soapCall'])
            ->getMock();
        $client->expects($this->once())
            ->method('__soapCall')
            ->with($this->equalTo('sugarPing'), [])
            ->willReturn('');
        $client->sugarPing();
    }
}
