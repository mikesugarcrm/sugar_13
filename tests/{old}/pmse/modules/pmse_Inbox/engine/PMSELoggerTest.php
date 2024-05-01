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
use PHPUnit\Framework\Error\Error;

class PMSELoggerTest extends TestCase
{
    public function testGetInstance()
    {
        $instance = PMSELogger::getInstance();
        $this->assertInstanceOf('PMSELogger', $instance);
    }

    public function testClone()
    {
        $this->expectException(Error::class);
        $instance = PMSELogger::getInstance();
        clone($instance);
    }

    public function testLog()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['formatMessage', 'write'])
            ->getMock();
        $loggerMock->expects($this->once())
            ->method('write');
        $loggerMock->setLogLevel(LogLevel::ALERT);
        $loggerMock->log(LogLevel::EMERGENCY, 'some message');
    }

    public function testLogInvalid()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['formatMessage', 'write'])
            ->getMock();
        $loggerMock->setLogLevel(LogLevel::EMERGENCY);
        $loggerMock->log(LogLevel::ALERT, 'some message');
    }

    public function testLogInvalidContext()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['write'])
            ->getMock();

        $loggerMock->expects($this->once())
            ->method('write');
        $loggerMock->setLogLevel(LogLevel::ALERT);
        $loggerMock->log(LogLevel::EMERGENCY, 'some message', []);
    }

    public function testWrite()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $writerMock = $this->getMockBuilder('PMSELogWriter')
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $writerMock->expects($this->once())
            ->method('log');

        $loggerMock->setLogWriter($writerMock);

        $loggerMock->write(LogLevel::EMERGENCY, 'Some Message');
    }
}
