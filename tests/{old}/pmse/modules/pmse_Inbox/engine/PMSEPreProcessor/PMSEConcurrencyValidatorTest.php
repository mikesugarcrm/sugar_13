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

use Sugarcrm\Sugarcrm\ProcessManager\Registry;
use PHPUnit\Framework\TestCase;

class PMSEConcurrencyValidatorTest extends TestCase
{
    private $validator;

    private $registry;

    /**
     * Sets up the test data, for example,
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->registry = Registry\Registry::getInstance();
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->registry->reset();
    }

    /**
     * Test if a flow is being concurrently requested by the direct handler class
     */
    public function testValidateRequestIfConcurrent()
    {
        $this->validator = $this->getMockBuilder('PMSEConcurrencyValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug'])
            ->getMock();

        $this->registry->set('locked_flows', ['abc123' => 1]);

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->disableOriginalConstructor()
            ->setMethods(['getArguments'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue(['idFlow' => 'abc123']));

        $this->validator->setLogger($loggerMock);
        $result = $this->validator->validateRequest($requestMock);
        $this->assertEquals(false, $result->isValid());
    }

    /**
     * Test if no concurrent flows are being requested by the direct handler class
     */
    public function testValidateRequestIfNotConcurrent()
    {
        $this->validator = $this->getMockBuilder('PMSEConcurrencyValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug'])
            ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->disableOriginalConstructor()
            ->setMethods(['getArguments'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue(['idFlow' => 'abc123']));

        $this->validator->setLogger($loggerMock);
        $result = $this->validator->validateRequest($requestMock);
        $this->assertEquals(true, $result->isValid());
    }
}
