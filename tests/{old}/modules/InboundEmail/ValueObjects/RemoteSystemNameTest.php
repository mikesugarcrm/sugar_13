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

class RemoteSystemNameTest extends TestCase
{
    public function validRemoteSystemNames()
    {
        return [
            ['localhost'],
            ['127.0.0.1'],
            ['imap.gmail.com'],
            ['my-imap-server'],
        ];
    }

    /**
     * @dataProvider validRemoteSystemNames
     * @param $name
     */
    public function testValidRemoteSystemName($name)
    {
        $remoteSystemName = RemoteSystemName::fromString($name);
        $this->assertEquals($name, $remoteSystemName->value());
    }


    public function invalidRemoteSystemNames()
    {
        return [
            ['localhost foo'],
            ['x -oProxyCommand=echo\tZWNobyAnMTIzNDU2Nzg5MCc+L3RtcC90ZXN0MDAwMQo=|base64\t-d|sh}'],
        ];
    }

    /**
     * @dataProvider invalidRemoteSystemNames
     * @param $name
     */
    public function testInvalidRemoteSystemName($name)
    {
        $this->expectException(DomainException::class);
        $remoteSystemName = RemoteSystemName::fromString($name);
    }
}
