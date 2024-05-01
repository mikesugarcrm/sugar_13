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

class PMSEFormResponseParserTest extends TestCase
{
    /**
     * @var array<int, array<string, int|string>>|mixed|mixed[]
     */
    public $resultArray;
    protected $dataParser;

    protected function setUp(): void
    {
        $this->dataParser = ProcessManager\Factory::getPMSEObject('PMSEFormResponseParser');
        $this->resultArray = [
            [
                'act_uid' => 'fjhsd892ddsdsjxd9891221',
                'act_id' => 13,
                'frm_action' => 'APPROVE',
            ],
            [
                'act_uid' => 'as7yed2839jh9828988912a',
                'act_id' => 14,
                'frm_action' => 'REJECT',
            ],
            [
                'act_uid' => 'hjhsd892dj9821j8988912j',
                'act_id' => 12,
                'frm_action' => 'ROUTE',
            ],
        ];
    }

    public function testParseCriteriaTokenRoute()
    {
        $args = [];
        $dbMock = $this->getMockBuilder('db')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $dbMock->resultArray = $this->resultArray;

        $dbMock->expects($this->exactly(1))
            ->method('Query')
            ->with($this->isType('string'))
            ->will($this->returnValue($this->resultArray));

        $dbMock->method('fetchByAssoc')
            ->with($this->isType('array'))
            ->willReturnOnConsecutiveCalls($this->resultArray[2]);

        $args['db'] = $dbMock;
        $args['cas_id'] = 15;

        $criteriaToken = new stdClass();
        $criteriaToken->expLabel = '{::_form_::hjhsd892dj9821j8988912j::} == "ROUTE"';
        $criteriaToken->expField = 'hjhsd892dj9821j8988912j';

        $expectedToken = new stdClass();
        $expectedToken->expLabel = '{::_form_::hjhsd892dj9821j8988912j::} == "ROUTE"';
        $expectedToken->expToken = '{::_form_::hjhsd892dj9821j8988912j::}';
        $expectedToken->currentValue = ['ROUTE'];

        $resultCriteriaToken = $this->dataParser->parseCriteriaToken($criteriaToken, $args);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->currentValue);
    }

    public function testParseCriteriaTokenApprove()
    {
        $args = [];
        $dbMock = $this->getMockBuilder('db')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $dbMock->resultArray = $this->resultArray;

        $dbMock->expects($this->exactly(1))
            ->method('Query')
            ->with($this->isType('string'))
            ->will($this->returnValue($this->resultArray));

        $dbMock->method('fetchByAssoc')
            ->with($this->isType('array'))
            ->willReturnOnConsecutiveCalls($this->resultArray[0]);

        $args['db'] = $dbMock;
        $args['cas_id'] = 2;

        $criteriaToken = new stdClass();
        $criteriaToken->expLabel = '{::_form_::fjhsd892ddsdsjxd9891221::} == "APPROVE"';
        $criteriaToken->expField = 'fjhsd892ddsdsjxd9891221';

        $expectedToken = new stdClass();
        $expectedToken->expLabel = '{::_form_::fjhsd892ddsdsjxd9891221::} == "APPROVE"';
        $expectedToken->expToken = '{::_form_::fjhsd892ddsdsjxd9891221::}';
        $expectedToken->currentValue = ['APPROVE'];

        $resultCriteriaToken = $this->dataParser->parseCriteriaToken($criteriaToken, $args);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->currentValue);
    }

    public function testParseCriteriaTokenReject()
    {
        $args = [];
        $dbMock = $this->getMockBuilder('db')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $dbMock->resultArray = $this->resultArray;

        $dbMock->expects($this->exactly(1))
            ->method('Query')
            ->with($this->isType('string'))
            ->will($this->returnValue($this->resultArray));

        $dbMock->method('fetchByAssoc')
            ->with($this->isType('array'))
            ->willReturnOnConsecutiveCalls($this->resultArray[1]);

        $args['db'] = $dbMock;
        $args['cas_id'] = 15;

        $criteriaToken = new stdClass();
        $criteriaToken->expLabel = '{::_form_::as7yed2839jh9828988912a::} == "REJECT"';
        $criteriaToken->expField = 'as7yed2839jh9828988912a';

        $expectedToken = new stdClass();
        $expectedToken->expLabel = '{::_form_::as7yed2839jh9828988912a::} == "REJECT"';
        $expectedToken->expToken = '{::_form_::as7yed2839jh9828988912a::}';
        $expectedToken->currentValue = ['REJECT'];

        $resultCriteriaToken = $this->dataParser->parseCriteriaToken($criteriaToken, $args);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->currentValue);
    }

    public function testParseCriteriaTokens()
    {
        $args = [];
        $dbMock = $this->getMockBuilder('db')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $dbMock->resultArray = $this->resultArray;

        $dbMock->expects($this->exactly(1))
            ->method('Query')
            ->with($this->isType('string'))
            ->will($this->returnValue($this->resultArray));

        $dbMock->method('fetchByAssoc')
            ->with($this->isType('array'))
            ->willReturnOnConsecutiveCalls($this->resultArray[1]);

        $args['db'] = $dbMock;
        $args['cas_id'] = 15;

        $criteriaToken = new stdClass();
        $criteriaToken->expLabel = '{::_form_::as7yed2839jh9828988912a::} == "REJECT"';
        $criteriaToken->expField = 'as7yed2839jh98289889100';

        $expectedToken = new stdClass();
        $expectedToken->expLabel = '{::_form_::as7yed2839jh9828988912a::} == "REJECT"';
        $expectedToken->expToken = '{::_form_::as7yed2839jh9828988912a::}';
        $expectedToken->currentValue = ['REJECT'];

        $resultCriteriaToken = $this->dataParser->parseCriteriaToken($criteriaToken, $args);
        $this->assertEquals([''], $resultCriteriaToken->currentValue);
    }
}
