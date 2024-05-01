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

/*
 * Tests the getStatus() function of SugarSNIP to ensure that the return value is correct for various server responses.
 */

class SnipStatusTest extends TestCase
{
    private $snip;

    protected function setUp(): void
    {
        $this->snip = SugarSNIP::getInstance();
    }

    public function testStatusPurchased()
    {
        $this->statusTest(json_encode(['result' => 'ok', 'status' => 'success']), 'purchased');
    }

    public function testStatusNotPurchased1()
    {
        $this->statusTest(json_encode(['result' => 'instance not found']), 'notpurchased');
    }

    public function testStatusNotPurchased2()
    {
        $this->statusTest(json_encode(['result' => 'instance not found', 'status' => 'fasdfkuaseyrkajsdfh udd']), 'notpurchased');
    }

    public function testStatusDownShowEnableScreen()
    {
        $this->statusTest(json_encode(['result' => 'asdofi7aso8fdus', 'status' => 'dafso8dfuds']), 'notpurchased', null, false);
    }

    public function testStatusPurchasedError1()
    {
        $this->statusTest(json_encode(['result' => 'ok']), 'purchased_error', null);
    }

    public function testStatusPurchasedError2()
    {
        $this->statusTest(json_encode(['result' => 'ok', 'status' => 'this is a test error status']), 'purchased_error', 'this is a test error status');
    }


    protected function statusTest($serverResponse, $expectedStatus, $expectedMessage = null, $snipEmailExists = true)
    {
        //give snip our mock client
        $clientMock = $this->createMock('SugarHttpClient');
        $clientMock->expects($this->once())
            ->method('callRest')
            ->with($this->matchesRegularExpression('`^' . $this->snip->getSnipURL() . 'status/?`'))
            ->will($this->returnValue($serverResponse));
        $this->snip->setClient($clientMock);
        $oldemail = $this->snip->getSnipEmail();
        if ($snipEmailExists) {
            $this->snip->setSnipEmail('snip-test-182391820@sugarcrm.com');
        } else {
            $this->snip->setSnipEmail('');
        }

        //call getStatus on snip
        $status = $this->snip->getStatus();

        $this->snip->setSnipEmail($oldemail);

        //check to make sure the status is an array with the correct values
        $this->assertTrue(is_array($status), "getStatus() should always return an associative array of the form array('status'=>string,'message'=>string|null). But it did not return an array.");
        $this->assertEquals($expectedStatus, $status['status'], "Expected status: '$expectedStatus'. Returned status: '{$status['status']}'");
        $this->assertEquals($expectedMessage, $status['message'], 'Expected message: ' . (is_null($expectedMessage) ? 'null' : "'$expectedMessage'") . ". Returned message: '{$status['message']}'");
    }
}
