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

class PMSEEventDefinitionWrapperTest extends TestCase
{
    protected $fixtureArray;
    protected $arguments;
    protected $mockElement;
    protected $mockDefinition;
    protected $mockCrmDataWrapper;

    protected function setUp(): void
    {
        $this->mockElement = $this->getMockBuilder('pmse_BpmnEvent')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'retrieve', 'save'])
            ->getMock();

        $this->mockDefinition = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'retrieve', 'save'])
            ->getMock();

        $this->mockCrmDataWrapper = $this->getMockBuilder('PMSECrmDataWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getRelatedSearch'])
            ->getMock();

        $this->arguments = ['record' => '1'];
    }

    public function testGet()
    {
        $mockEventDefinitionWrapper = $this->getMockBuilder('PMSEEventDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->mockElement->expects($this->once())
            ->method('retrieve_by_string_fields');

        $this->mockElement->id = 'event01';
        $this->mockElement->fetched_row = [
            'id' => 'event01',
            'name' => ' some event',
            'evn_uid' => 'eventUID01',
        ];

        $this->mockDefinition->expects($this->once())
            ->method('retrieve');

        $this->mockDefinition->fetched_row = [
            'id' => 'event01',
            'name' => ' some event',
            'evn_uid' => 'eventUID01',
        ];

        $this->mockCrmDataWrapper->expects($this->exactly(2))
            ->method('getRelatedSearch');

        $mockEventDefinitionWrapper->setCrmDataWrapper($this->mockCrmDataWrapper);
        $mockEventDefinitionWrapper->setEvent($this->mockElement);
        $mockEventDefinitionWrapper->setEventDefinition($this->mockDefinition);

        $args = [
            'record' => 'event01',
            'related' => 'Leads,Meetings',
        ];

        $result = $mockEventDefinitionWrapper->_get($args);
    }

    public function testPutNormalEvent()
    {
        $mockEventDefinitionWrapper = $this->getMockBuilder('PMSEEventDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->mockElement->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $this->mockElement->id = 'event01';
        $this->mockElement->fetched_row = [
            'id' => 'event01',
            'name' => ' some event',
            'evn_uid' => 'eventUID01',
        ];

        $mockEventDefinitionWrapper->setCrmDataWrapper($this->mockCrmDataWrapper);
        $mockEventDefinitionWrapper->setEvent($this->mockElement);
        $mockEventDefinitionWrapper->setEventDefinition($this->mockDefinition);

        $args = [
            'record' => 'event01',
            'data' => [],
        ];

        $result = $mockEventDefinitionWrapper->_put($args);
    }

    public function testPutEmptyTimerType()
    {
        $mockEventDefinitionWrapper = $this->getMockBuilder('PMSEEventDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->mockElement->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $this->mockElement->id = 'event01';
        $this->mockElement->fetched_row = [
            'id' => 'event01',
            'name' => ' some event',
            'evn_uid' => 'eventUID01',
        ];

        $mockEventDefinitionWrapper->setCrmDataWrapper($this->mockCrmDataWrapper);
        $mockEventDefinitionWrapper->setEvent($this->mockElement);
        $mockEventDefinitionWrapper->setEventDefinition($this->mockDefinition);

        $args = [
            'record' => 'event01',
            'data' => [
                'evn_timer_type' => '',
            ],
        ];

        $result = $mockEventDefinitionWrapper->_put($args);
    }

    public function testPutDuration()
    {
        $mockEventDefinitionWrapper = $this->getMockBuilder('PMSEEventDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->mockElement->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $this->mockElement->id = 'event01';
        $this->mockElement->fetched_row = [
            'id' => 'event01',
            'name' => ' some event',
            'evn_uid' => 'eventUID01',
        ];

        $mockEventDefinitionWrapper->setCrmDataWrapper($this->mockCrmDataWrapper);
        $mockEventDefinitionWrapper->setEvent($this->mockElement);
        $mockEventDefinitionWrapper->setEventDefinition($this->mockDefinition);

        $args = [
            'record' => 'event01',
            'data' => [
                'evn_timer_type' => 'duration',
                'evn_duration_criteria' => 'Some Criteria',
                'evn_duration_params' => [],
            ],

        ];
        $result = $mockEventDefinitionWrapper->_put($args);
    }

    public function testPutFixedDate()
    {
        $mockEventDefinitionWrapper = $this->getMockBuilder('PMSEEventDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->mockElement->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue(true));

        $this->mockElement->id = 'event01';
        $this->mockElement->fetched_row = [
            'id' => 'event01',
            'name' => ' some event',
            'evn_uid' => 'eventUID01',
        ];

        $mockEventDefinitionWrapper->setCrmDataWrapper($this->mockCrmDataWrapper);
        $mockEventDefinitionWrapper->setEvent($this->mockElement);
        $mockEventDefinitionWrapper->setEventDefinition($this->mockDefinition);

        $args = [
            'record' => 'event01',
            'data' => [
                'evn_timer_type' => 'fixed date',
                'evn_criteria' => 'Some fixed criteria',
            ],
        ];

        $result = $mockEventDefinitionWrapper->_put($args);
    }

    public function testNotify()
    {
        $mockEventDefinitionWrapper = $this->getMockBuilder('PMSEEventDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $observerMock = $this->getMockBuilder('PMSEObserver')
            ->disableOriginalConstructor()
            ->setMethods(['update'])
            ->getMock();

        $observerMock->expects($this->once())
            ->method('update');

        $mockEventDefinitionWrapper->attach($observerMock);
        $mockEventDefinitionWrapper->notify();
    }

    public function testDetach()
    {
        $mockEventDefinitionWrapper = $this->getMockBuilder('PMSEEventDefinitionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $observerMock = $this->getMockBuilder('PMSEObserver')
            ->disableOriginalConstructor()
            ->setMethods(['update'])
            ->getMock();

        $observerMock->expects($this->once())
            ->method('update');

        $mockEventDefinitionWrapper->attach($observerMock);
        $mockEventDefinitionWrapper->notify();
        $mockEventDefinitionWrapper->detach($observerMock);
        $mockEventDefinitionWrapper->notify();
    }
}
