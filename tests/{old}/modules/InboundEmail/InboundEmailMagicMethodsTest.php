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
 * This class is meant to test that \InboundEmail::$server_url may be get and set via magic methods
 */
class InboundEmailMagicMethodsTest extends TestCase
{
    /**
     * Ensure that server_url property can be set and get using magic methods
     */
    public function testVaildServerUrl()
    {
        $serverUrl = 'imap.gmail.com';
        $inboundEmail = new InboundEmail();
        $inboundEmail->server_url = $serverUrl;
        $this->assertTrue(isset($inboundEmail->server_url));
        $this->assertEquals($serverUrl, $inboundEmail->server_url);
    }

    /**
     * Ensure that the $server_url property can be set to an empty value
     */
    public function testEmptyServerUrl()
    {
        $inboundEmail = new InboundEmail();
        $inboundEmail->server_url = '';

        $this->assertFalse(isset($inboundEmail->server_url));
        $this->assertNull($inboundEmail->server_url);
    }

    /**
     * Ensure that server_url property can not be set to invalid value by magic setter
     */
    public function testInvalidServerUrl()
    {
        $inboundEmail = new InboundEmail();
        $inboundEmail->server_url = 'x -oProxyCommand=echo\tZWNobyAnMTIzNDU2Nzg5MCc+L3RtcC90ZXN0MDAwMQo=|base64\t-d|sh}';
        $this->assertNull($inboundEmail->server_url);
    }
}
