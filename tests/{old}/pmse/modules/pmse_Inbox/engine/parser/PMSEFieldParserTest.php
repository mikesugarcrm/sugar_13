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

class PMSEFieldParserTest extends TestCase
{
    protected $dataParser;
    protected $beanAccount;

    protected function setUp(): void
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->setMethods(null)
            ->getMock();
        $this->beanAccount = SugarTestAccountUtilities::createAccount();
        $beanContact1 = SugarTestContactUtilities::createContact('111');
        $beanContact2 = SugarTestContactUtilities::createContact('222');
        $this->beanAccount->load_relationship('contacts');
        $this->beanAccount->contacts->add($beanContact1);
        $this->beanAccount->contacts->add($beanContact2);
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * @param string $expRel
     * @param string $link
     * @param array $args
     * @param array $expectedResultArr
     * @covers ::getRelationshipChangeBean
     * @dataProvider getRelationshipChangeBeanDataProvider
     */
    public function testGetRelationshipChangeBean($expRel, $link, $args, $expectedResultArr)
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->setMethods(['checkRelationshipChange'])
            ->getMock();
        $resultArr = [];
        $obj = json_decode('{"expRel": "' . $expRel . '"}');
        $this->dataParser->setCriteriaToken($obj);
        $this->dataParser->setEvaluatedBean($this->beanAccount);
        $this->dataParser->expects($this->any())
            ->method('checkRelationshipChange')
            ->willReturn(true);

        PMSEBaseValidator::setLogicHookArgs($args);
        $results = $this->dataParser->getRelationshipChangeBean($link);

        foreach ($results as $result) {
            $resultArr[] = $result->id;
        }

        $this->assertEqualsCanonicalizing($expectedResultArr, $resultArr);
    }

    /**
     * getRelatedBeanDataProvider
     * @return array[]
     */
    public function getRelationshipChangeBeanDataProvider()
    {
        // $expRel, $link, $args, $expectedResultArr
        return [
            ['Added', 'contacts', ['link' => 'contacts', 'related_module' => 'Contacts', 'related_id' => '111',], ['111']],
            ['Added', 'contacts', ['link' => 'calls', 'related_module' => 'Calls', 'related_id' => '111',], []],
            ['', 'contacts', [], []],
        ];
    }

    /**
     * @param String $expRel
     * @param String $event
     * @param bool $expectedResult
     * @covers ::checkRelationshipChange
     * @dataProvider checkRelationshipChangeDataProvider
     */
    public function testCheckRelationshipChange($expRel, $event, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->dataParser->checkRelationshipChange($expRel, $event));
    }

    /**
     * checkRelationshipChangeDataProvider
     * @return array[]
     */
    public function checkRelationshipChangeDataProvider()
    {
        // $expRel, $event, $expectedResult
        return [
            [
                'Added', 'after_save', false,
            ],
            [
                'Removed', 'after_delete', false,
            ],
            [
                'Added', 'after_delete', false,
            ],
            [
                'Removed', 'after_save', false,
            ],
            [
                'AddedOrRemoved', 'after_save', false,
            ],
            [
                'Removed', 'after_relationship_delete', true,
            ],
        ];
    }

    public function testParseCriteriaEqual()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('10/10/2013'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
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
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "date",
                "expOperator": "equals",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 =>
                (object)
                [
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'account_name',
                    'expOperator' => 'equals',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'expToken' => '{::future::Leads::account_name::}',
                    'currentValue' => '10/10/2013',
                ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaNotEquals()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "not_equals",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 =>
                (object)
                [
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'account_name',
                    'expOperator' => 'not_equals',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'expToken' => '{::future::Leads::account_name::}',
                    'currentValue' => 'ROCKSTAR',
                ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMajorEqualThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'major_equals_than',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMinorEqualThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'minor_equals_than',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMinorThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'minor_than',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMajorThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'major_than',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaDistinct()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('10/10/2013'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->datetime = '10/10/2013';
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
            'datetime' => [
                'type' => 'datetime',
            ],
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "datetime",
                "expOperator": "not_equals",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: != \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);

        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => '10/10/2013',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMajorEquals()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: >= \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMajor()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: > \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: > "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMinorEquals()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));


        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: <= \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteriaToken($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMinor()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: < \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaWithin()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "within",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: within \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: within "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaNotWithin()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "not_within",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: not_within \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: not_within "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }


    public function testParseCriteriaDefault()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(['parseTokenValue'])
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = ['Leads' => 'Lead'];
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
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
        ];
        $preCondition = json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "equal",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = [
            0 => (object)[
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equal',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: not_within "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ],
        ];
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseTokenValue()
    {
        $preferencesArray = [];
        $beanList = ['Leads' => 'Lead'];
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->parent_type = 'Opprtunities';
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
        ];

        $token = ['Leads', 'email_addresses_primary'];
        $expectedToken = ['rock.star@gmail.com'];
        $this->dataParser->setEvaluatedBean($beanObject);
        $this->dataParser->setBeanList($beanList);
        $processedToken = $this->dataParser->parseTokenValue($token);
        $this->assertEquals($expectedToken, $processedToken);
    }

    /**
     * @covers PMSEFieldParser::parseTokenValue
     */
    public function testParseTokenValueNull()
    {
        $beanList = ['Leads' => 'Lead'];
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $beanObject->email_addresses_primary = 'anything';

        // set before value to null
        $beanObject->dataChanges = ['email_addresses_primary' => ['before' => null]];

        $token = ['Leads', 'email_addresses_primary', 'changes_from'];
        $this->dataParser->setEvaluatedBean($beanObject);
        $this->dataParser->setBeanList($beanList);
        $processedToken = $this->dataParser->parseTokenValue($token);

        // should return empty string instead of null
        $this->assertSame('', $processedToken[0]);
    }

    public function testParseTokenValueToken()
    {
        $preferencesArray = [];
        $beanList = ['Leads' => 'Lead', 'Notes' => 'Notes'];
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->do_not_call = 'true';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->parent_type = 'Opprtunities';
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
        ];

        $token = ['Leads', 'do_not_call'];
        $expectedToken = [true];
        $this->dataParser->setEvaluatedBean($beanObject);
        $this->dataParser->setBeanList($beanList);
        $processedToken = $this->dataParser->parseTokenValue($token);
        $this->assertSame($expectedToken, $processedToken);
    }

    public function testParseTokenValueTokenEmptyModules()
    {
        $preferencesArray = [];
        $beanList = ['Leads' => 'Lead', 'Notes' => 'Notes'];
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->do_not_call = 'true';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Notes';
        $beanObject->parent_type = 'Opprtunities';
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
        ];

        $token = ['Leads', 'do_not_call'];
        $expectedToken = [true];
        $this->dataParser->setEvaluatedBean($beanObject);
        $this->dataParser->setBeanList($beanList);
        $processedToken = $this->dataParser->parseTokenValue($token);
        $this->assertSame($expectedToken, $processedToken);
    }

    public function testDecomposeToken()
    {
        $token = '{::future::Leads::email_addresses_primary::}';
        $expectedToken = ['future', 'Leads', 'email_addresses_primary'];
        $processedToken = $this->dataParser->decomposeToken($token);
        $this->assertEquals($expectedToken, $processedToken);
    }

    public function testDecomposeTokenEmpty()
    {
        $token = '';
        $expectedToken = [];
        $processedToken = $this->dataParser->decomposeToken($token);
        $this->assertEquals($expectedToken, $processedToken);
    }
}
