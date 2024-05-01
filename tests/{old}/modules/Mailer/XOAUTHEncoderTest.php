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
 * @group email
 * @group mailer
 */
class XOAUTHEncoderTest extends TestCase
{
    /**
     * @covers ::getOauth64
     */
    public function testGetOAuth64()
    {
        $mockUserName = 'fake_user_name';
        $mockAccessToken = 'fake_access_token';

        $mockXOAUTH = $this->getMockBuilder('XOAUTHEncoder')
            ->setConstructorArgs([$mockUserName, $mockAccessToken])
            ->onlyMethods([])
            ->getMock();

        // Assert that getOauth64 correctly returns the base64 encoding of the
        // username and access token in XOAUTH2 format
        $expected = base64_encode('user=' . $mockUserName . "\001auth=Bearer " . $mockAccessToken . "\001\001");
        $this->assertEquals($expected, $mockXOAUTH->getOauth64());
    }
}
