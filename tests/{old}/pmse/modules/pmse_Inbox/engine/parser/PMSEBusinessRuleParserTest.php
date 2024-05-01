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

class PMSEBusinessRuleParserTest extends TestCase
{
    /**
     * @var array<int, array<string, int|string>>|mixed|mixed[]
     */
    public $resultArray;
    protected $dataParser;

    protected function setUp(): void
    {
        $this->dataParser = ProcessManager\Factory::getPMSEObject('PMSEBusinessRuleParser');

        $this->resultArray = [
            [
                'act_uid' => 'fjhsd892ddsdsjxd9891221',
                'act_id' => 13,
                'frm_action' => '{
                            "type": "INT",
                            "value": 2000
                        }',
            ],
            [
                'act_uid' => 'as7yed2839jh9828988912a',
                'act_id' => 14,
                'frm_action' => '{
                            "type": "INT",
                            "value": 2000
                        }',
            ],
            [
                'act_uid' => 'hjhsd892dj9821j8988912j',
                'act_id' => 12,
                'frm_action' => '{
                            "type": "INT",
                            "value": 2000
                        }',
            ],
        ];
    }

    public function testParseCriteriaToken()
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
            ->will($this->returnValue($this->resultArray[0]));
        $businessRule = json_decode('[{
                            "expDirection": "after",
                            "expFieldType": "INT",
                            "expModule": "Opportunities",
                            "expField": "fjhsd892ddsdsjxd9891221",
                            "expOperator": "major_equals_than",
                            "expValue": 2000,
                            "expType": "MODULE",
                            "expLabel": "amount >= 2000"
                        }]');
        //        $expectedRule = '[{
        //                            "expDirection": "after",
        //                            "expFieldType": "INT",
        //                            "expModule": "Opportunities",
        //                            "expField": "amount",
        //                            "expOperator": "major_equals_than",
        //                            "expValue": 2000,
        //                            "expType": "MODULE",
        //                            "expLabel": "amount >= 2000"
        //                            "expToken": "{::future::Opportunities::amount::}"
        //                            "currentValue": 1200
        //                        }]';
        $args['db'] = $dbMock;
        $args['cas_id'] = 15;
        $expectedToken = new stdClass();
        $expectedToken->expToken = '{::_form_::fjhsd892ddsdsjxd9891221::}';
        $expectedToken->currentValue = [2000];
        $resultCriteriaToken = $this->dataParser->parseCriteriaToken($businessRule[0], $args);
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
            ->willReturnOnConsecutiveCalls($this->resultArray[0]);
        $businessRule = json_decode('[{
                            "expDirection": "after",
                            "expFieldType": "INT",
                            "expModule": "Opportunities",
                            "expField": "fjhsd892ddsdsjxd9891232",
                            "expOperator": "major_equals_than",
                            "expValue": 2000,
                            "expType": "MODULE",
                            "expLabel": "amount >= 2000"
                        }]');
        $args['db'] = $dbMock;
        $args['cas_id'] = 15;
        $expectedToken = new stdClass();
        $expectedToken->expToken = '{::_form_::fjhsd892ddsdsjxd9891221::}';
        $expectedToken->currentValue = [2000];
        $resultCriteriaToken = $this->dataParser->parseCriteriaToken($businessRule[0], $args);
        $this->assertEquals([''], $resultCriteriaToken->currentValue);
    }

    public function testProcessValueExpression()
    {
        $businessRule = json_decode('[{
                            "expDirection": "after",
                            "expFieldType": "INT",
                            "expModule": "Opportunities",
                            "expField": "amount",
                            "expOperator": "major_equals_than",
                            "expValue": 2000,
                            "expType": "MODULE",
                            "expLabel": "amount >= 2000"
                        }]');

        $expectedToken = new stdClass();
        $expectedToken->currentValue = 2000;
        $resultCriteriaToken = $this->dataParser->processValueExpression($businessRule[0]);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->expValue);

        $businessRule = json_decode('[{
                            "expDirection": "after",
                            "expFieldType": "FLOAT",
                            "expModule": "Opportunities",
                            "expField": "amount",
                            "expOperator": "major_equals_than",
                            "expValue": 20.98,
                            "expType": "MODULE",
                            "expLabel": "amount >= 2000"
                        }]');

        $expectedToken = new stdClass();
        $expectedToken->currentValue = 20.98;
        $resultCriteriaToken = $this->dataParser->processValueExpression($businessRule[0]);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->expValue);

        $businessRule = json_decode('[{
                            "expDirection": "after",
                            "expFieldType": "DOUBLE",
                            "expModule": "Opportunities",
                            "expField": "amount",
                            "expOperator": "major_equals_than",
                            "expValue": 20.48,
                            "expType": "MODULE",
                            "expLabel": "amount >= 2000"
                        }]');

        $expectedToken = new stdClass();
        $expectedToken->currentValue = 20.48;
        $resultCriteriaToken = $this->dataParser->processValueExpression($businessRule[0]);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->expValue);

        $businessRule = json_decode('[{
                            "expDirection": "after",
                            "expFieldType": "BOOL",
                            "expModule": "Opportunities",
                            "expField": "amount",
                            "expOperator": "major_equals_than",
                            "expValue": "true",
                            "expType": "MODULE",
                            "expLabel": "amount >= 2000"
                        }]');

        $expectedToken = new stdClass();
        $expectedToken->currentValue = true;
        $resultCriteriaToken = $this->dataParser->processValueExpression($businessRule[0]);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->expValue);

        $businessRule = json_decode('[{
                            "expDirection": "after",
                            "expFieldType": "STRING",
                            "expModule": "Opportunities",
                            "expField": "amount",
                            "expOperator": "major_equals_than",
                            "expValue": "Holas",
                            "expType": "MODULE",
                            "expLabel": "amount >= 2000"
                        }]');

        $expectedToken = new stdClass();
        $expectedToken->currentValue = 'Holas';
        $resultCriteriaToken = $this->dataParser->processValueExpression($businessRule[0]);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken->expValue);
    }
}
