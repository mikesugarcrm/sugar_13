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

class PMSEDataParserGatewayTest extends TestCase
{
    /**
     * @var array<int, array<string, int|string>>|mixed|mixed[]
     */
    public $resultArray;
    /**
     * @var array<int, array<string, int|string>>|mixed|mixed[]
     */
    public $resultArrayBR;
    private $parserGateway;

    protected function setUp(): void
    {
        $this->parserGateway = ProcessManager\Factory::getPMSEObject('PMSEDataParserGateway');
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

        $this->resultArrayBR = [
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

    /**
     * value empty
     */
    public function testParseCriteriaArrayEmpty()
    {
        $criteriaArray = [];
        $bean = new stdClass();
        $currentUser = new stdClass();
        $resultCriteria = $this->parserGateway->parseCriteriaArray($criteriaArray, $bean, $currentUser);
        $this->assertEquals($resultCriteria, []);

        $criteriaToken = new stdClass();
        $criteriaToken->expType = 'default';

        $criteriaArray[] = $criteriaToken;
        $resultCriteria = $this->parserGateway->parseCriteriaArray($criteriaArray, $bean, $currentUser);
        $this->assertEquals($resultCriteria, $criteriaArray);
    }

    /**
     * value empty
     */
    public function testParseCriteriaArrayUserAdmin()
    {
        $criteriaArray = [];
        $bean = new stdClass();
        $currentUser = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUser->is_admin = 1;
        $criteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": null,
            "expType": "USER_ADMIN",
            "expLabel": "Current user is admin"
        }');
        $criteriaArray[] = $criteriaToken;
        $resultCriteria = $this->parserGateway->parseCriteriaArray($criteriaArray, $bean, $currentUser);
        $expectedCriteriaToken = json_decode('{
            "expModule": null,
            "expField": "current_user",
            "expOperator": "equals",
            "expValue": "is_admin",
            "expType": "USER_ADMIN",
            "expLabel": "Current user is admin",
            "expToken": "{::future::Users::id::}",
            "currentValue": "is_admin"
        }');
        $this->assertEquals($resultCriteria, [$expectedCriteriaToken]);
    }

    public function testParseCriteriaArrayControl()
    {
        $criteriaArray = [];
        $bean = new stdClass();

        $currentUser = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUser->is_admin = 1;

        $dbMock = $this->getMockBuilder('db')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $dbMock->expects($this->exactly(1))
            ->method('Query')
            ->with($this->isType('string'))
            ->will($this->returnValue($this->resultArray));

        $dbMock->expects($this->at(1))
            ->method('fetchByAssoc')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->resultArray[2]));

        $criteriaToken = new stdClass();
        $criteriaToken->expLabel = '{::_form_::hjhsd892dj9821j8988912j::} == "ROUTE"';
        $criteriaToken->expField = 'hjhsd892dj9821j8988912j';
        $criteriaToken->expType = 'CONTROL';

        $expectedToken = new stdClass();
        $expectedToken->expLabel = '{::_form_::hjhsd892dj9821j8988912j::} == "ROUTE"';
        $expectedToken->expToken = '{::_form_::hjhsd892dj9821j8988912j::}';
        $criteriaToken->expType = 'CONTROL';
        $expectedToken->currentValue = 'ROUTE';

        $criteriaArray[] = $criteriaToken;
        $resultCriteria = $this->parserGateway->parseCriteriaArray($criteriaArray, $bean, $currentUser, [], ['db' => $dbMock, 'cas_id' => '15']);

        $this->assertEquals($resultCriteria[0]->currentValue, $expectedToken->currentValue);
    }

    public function testParseCriteriaArrayModule()
    {
        $criteriaArray = [];
        $beanList = ['Leads' => 'Lead'];

        $currentUser = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUser->is_admin = 1;

        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->date = '10/10/2013';
        $beanObject->field_defs = [
            'account_name' => [
                'type' => 'varchar',
                'dbtype' => 'char',
            ],
            'email_addresses_primary' => [
                'type' => 'varchar',
                'dbtype' => 'char',
            ],
            'phone_mobile' => [
                'type' => 'varchar',
                'dbtype' => 'char',
            ],
            'probability' => [
                'type' => 'int',
                'dbtype' => 'double',
            ],
            'amount' => [
                'type' => 'float',
                'dbtype' => 'double',
            ],
            'do_not_call' => [
                'type' => 'bool',
            ],
            'date' => [
                'type' => 'date',
            ],
        ];

        $beanObject->parent_type = '';

        $criteriaToken = new stdClass();
        $criteriaToken->expDirection = 'after';
        $criteriaToken->expModule = 'Leads';
        $criteriaToken->expField = 'date';
        $criteriaToken->expOperator = 'equals';
        $criteriaToken->expValue = 'ONE';
        $criteriaToken->expType = 'MODULE';
        $criteriaToken->expLabel = 'Account Name: == "ONE"';

        $criteriaArray[] = $criteriaToken;
        $processedCondition = $this->parserGateway->parseCriteriaArray($criteriaArray, $beanObject, $currentUser, $beanList, []);
        $postCondition = [
            0 =>
                (object)
                [
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'date',
                    'expOperator' => 'equals',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'currentValue' => ['10/10/2013'],
                    'expSubtype' => 'date',
                ],
        ];
        $this->assertEquals($postCondition[0], $processedCondition[0]);
    }

    public function testParseCriteriaArrayBR()
    {
        $args = [];
        $criteriaArray = [];
        $bean = new stdClass();
        $currentUser = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $currentUser->is_admin = 1;

        $dbMock = $this->getMockBuilder('db')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();
        $dbMock->resultArray = $this->resultArrayBR;
        $dbMock->expects($this->exactly(1))
            ->method('Query')
            ->with($this->isType('string'))
            ->will($this->returnValue($this->resultArrayBR));
        $dbMock->expects($this->at(1))
            ->method('fetchByAssoc')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->resultArrayBR[0]));

        $businessRule = new stdClass();
        $businessRule->expDirection = 'after';
        $businessRule->expFieldType = 'INT';
        $businessRule->expModule = 'Opportunities';
        $businessRule->expField = 'fjhsd892ddsdsjxd9891221';
        $businessRule->expOperator = 'major_equals_than';
        $businessRule->expValue = 2000;
        $businessRule->expType = 'BUSINESS_RULES';
        $businessRule->expLabel = 'amount >= 2000';

        $args['db'] = $dbMock;
        $args['cas_id'] = 15;
        $expectedToken = new stdClass();
        $expectedToken->expToken = '{::_form_::fjhsd892ddsdsjxd9891221::}';
        $expectedToken->currentValue = 2000;
        $criteriaArray[] = $businessRule;
        $resultCriteriaToken = $this->parserGateway->parseCriteriaArray($criteriaArray, $bean, $currentUser, [], $args);
        $this->assertEquals($expectedToken->currentValue, $resultCriteriaToken[0]->currentValue);
    }
}
