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

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

class PMSEValidatorTest extends TestCase
{
    protected $loggerMock;

    /**
     * Sets up the test data, for example,
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug'])
            ->getMock();
    }

    public function testValidateRequestInvalidRequest()
    {
        $validatorMock = $this->getMockBuilder('PMSEValidator')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveValidator'])
            ->getMock();

        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $request->setType('invalid_type');
        $validatorMock->setLogger($this->loggerMock);

        $result = $validatorMock->validateRequest($request);
        $this->assertEquals(false, $result);
    }

    public function testValidateRequestDirectAndValid()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $request->setType('direct');
        $resultRequest = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $resultRequest->validate();

        $validatorElementMock = $this->getMockBuilder('PMSEValidate')
            ->disableOriginalConstructor()
            ->setMethods(['validateRequest'])
            ->getMock();

        $validatorElementMock->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue($resultRequest));

        $validatorMock = $this->getMockBuilder('PMSEValidator')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveValidator'])
            ->getMock();

        $validatorMock->setValidators([
            'direct' => [
                'concurrency' => PMSEValidationLevel::Simple,
                'record' => PMSEValidationLevel::NoValidation,
                'element' => PMSEValidationLevel::NoValidation,
                'expression' => PMSEValidationLevel::NoValidation,
            ],
        ]);

        $validatorMock->expects($this->once())
            ->method('retrieveValidator')
            ->will($this->returnValue($validatorElementMock));

        $validatorMock->setLogger($this->loggerMock);
        $result = $validatorMock->validateRequest($request);
        $this->assertEquals('PROCESSED', $result->getStatus());
    }

    public function testValidateRequestDirectAndInvalid()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $request->setType('direct');
        $resultRequest = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $resultRequest->invalidate();

        $validatorElementMock = $this->getMockBuilder('PMSEValidate')
            ->disableOriginalConstructor()
            ->setMethods(['validateRequest'])
            ->getMock();

        $validatorElementMock->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue($resultRequest));

        $validatorMock = $this->getMockBuilder('PMSEValidator')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveValidator'])
            ->getMock();

        $validatorMock->setValidators([
            'direct' => [
                'concurrency' => PMSEValidationLevel::Simple,
                'record' => PMSEValidationLevel::NoValidation,
                'element' => PMSEValidationLevel::NoValidation,
                'expression' => PMSEValidationLevel::NoValidation,
            ],
        ]);

        $validatorMock->expects($this->once())
            ->method('retrieveValidator')
            ->will($this->returnValue($validatorElementMock));

        $validatorMock->setLogger($this->loggerMock);
        $result = $validatorMock->validateRequest($request);
        $this->assertEquals('INVALID', $result->getStatus());
    }
}
