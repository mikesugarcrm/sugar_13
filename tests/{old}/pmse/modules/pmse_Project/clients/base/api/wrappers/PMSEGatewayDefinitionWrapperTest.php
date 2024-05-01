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

class PMSEGatewayDefinitionWrapperTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\pmse_BpmnFlow|mixed
     */
    public $mocFlow;
    protected $gatDefWrapper;
    protected $fixtureArray;
    protected $arguments;
    protected $newId = '';
    protected $mocGateway;
    protected $mocGatewayDefinition;

    protected function setUp(): void
    {
        $this->mocGateway = $this->getMockBuilder('pmse_BpmnGateway')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $this->mocGateway->id = 1;
        $this->mocGatewayDefinition = $this->getMockBuilder('pmse_BpmGatewayDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mocGatewayDefinition->id = 1;
        $this->mocFlow = $this->getMockBuilder('pmse_BpmnFlow')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save', 'process_order_by', 'get_full_list'])
            ->getMock();
        $this->mocFlow->id = 1;
        $this->mocFlow->table_name = 'some_table';
        $this->gatDefWrapper = $this->getMockBuilder('PMSEGatewayDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getSelectRows'])
            ->getMock();
        $this->gatDefWrapper->setGateway($this->mocGateway);
        $this->gatDefWrapper->setGatewayDefinition($this->mocGatewayDefinition);
        $this->gatDefWrapper->setFlowBean($this->mocFlow);

        $this->arguments = ['id' => '1', 'record' => 1, 'data' => ['flo_uid' => '1', 'flo_condition' => 'a>0']];
    }

    public function testGet()
    {
        $this->gatDefWrapper->expects($this->any())
            ->method('getSelectRows')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        '1' => [
                            'flo_uid' => '1',
                            'flo_condition' => '{new condition}',
                        ],
                    ],
                ]
            ));

        $this->mocGateway->prj_id = 1;
        $this->mocGateway->prj_uid = '2193798123';
        $this->mocGateway->fetched_row = [
            'gat_id' => 1,
        ];
        $this->mocGateway->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mocGateway));
        $this->mocGateway->gat_id = '1234';

        $resultArray = [
            (object)[
                'flo_uid' => 'flo01',
                'flo_condition' => 'something == some_other_thing',
            ],
            (object)[
                'flo_uid' => 'flo02',
                'flo_condition' => 'something == some_other_thing_again',
            ],
        ];

        $this->mocFlow->expects($this->once())
            ->method('get_full_list')
            ->will($this->returnValue($resultArray));

        $result = $this->gatDefWrapper->_get($this->arguments);

        $this->assertIsArray($result);
        $this->assertEquals(2, count($result));

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(true, $result['success']);
    }

    public function testPut()
    {
        $this->mocGateway->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mocGateway));

        $this->mocGateway->prj_id = 1;
        $this->mocGateway->prj_uid = '2193798123';
        $this->mocGateway->fetched_row = [
            'gat_id' => 1,
            'prj_uid' => '2193798123',
        ];

        $arguments = ['id' => '1', 'record' => 1, 'data' => [['flo_uid' => '1', 'flo_condition' => 'a>0']]];

        $result = $this->gatDefWrapper->_put($arguments);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertEquals(true, $result['success']);
    }

    public function testPost()
    {
        $someArgs = [];
        $result = $this->gatDefWrapper->_post($someArgs);
        $expectedResult = [
            'success' => false,
        ];
        $this->assertIsArray($result);
        $this->assertEquals($expectedResult, $result);
    }
}
