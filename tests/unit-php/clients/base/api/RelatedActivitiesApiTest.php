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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarBean;

/**
 * Class RelatedActivitiesApiTest
 * @coversDefaultClass \RelatedActivitiesApi
 */
class RelatedActivitiesApiTest extends TestCase
{
    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->apiService = $this->createMock(\ServiceBase::class);
    }

    /**
     * @covers ::getRelatedActivities
     */
    public function testGetRelatedActivities(): void
    {
        $apiMock = $this->createPartialMock(
            \RelatedActivitiesApi::class,
            [
                'filterModuleList',
                'getAuditRecords',
                'getFullRecord',
                'getTimelineSettings',
                'getBeanFromArgs',
                'getDefaultModuleList',
            ]
        );
        $apiMock->method('filterModuleList')->willReturn([
            'records' => [
                [
                    'id' => 'id1',
                    '_module' => 'Meetings',
                ],
                [
                    '_module' => 'Audit',
                    'id' => 'id2',
                ],
                [
                    '_module' => 'Audit',
                    'id' => 'id3',
                ],
            ],
        ]);
        $apiMock->method('getAuditRecords')->willReturn([
            [
                '_module' => 'Audit',
                'id' => 'id2',
                'event_id' => 'id4',
                'field_name' => 'name',
                'data_type' => 'name',
                'before' => '',
                'after' => 'name1',
            ],
            [
                '_module' => 'Audit',
                'id' => 'id3',
                'event_id' => 'id4',
                'field_name' => 'desc',
                'data_type' => 'text',
                'before' => 'desc1',
                'after' => 'desc2',
            ],
        ]);
        $apiMock->method('getFullRecord')->willReturn([
            'id' => 'id1',
            'name' => 'name1',
            '_module' => 'Meetings',
        ]);
        $apiMock->method('getTimelineSettings')->willReturn([]);
        $apiMock->method('getDefaultModuleList')->willReturn([
            'meetings' => 'Meetings',
        ]);
        
        $args = ['module' => 'Cases', 'record' => 'r1'];
        $expected = [
            'records' => [
                [
                    'id' => 'id1',
                    'name' => 'name1',
                    '_module' => 'Meetings',
                ],
                [
                    '_module' => 'Audit',
                    'id' => 'id2',
                    'event_id' => 'id4',
                    'field_name' => 'name',
                    'data_type' => 'name',
                    'before' => '',
                    'after' => 'name1',
                    'event_action' => 'update',
                ],
                [
                    '_module' => 'Audit',
                    'id' => 'id3',
                    'event_id' => 'id4',
                    'field_name' => 'desc',
                    'data_type' => 'text',
                    'before' => 'desc1',
                    'after' => 'desc2',
                    'event_action' => 'update',
                ],
            ],
        ];
        $result = $apiMock->getRelatedActivities($this->apiService, $args);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::auditSetup
     */
    public function testAuditSetup(): void
    {
        $apiMock = $this->createPartialMock(
            \RelatedActivitiesApi::class,
            ['getAuditFields', 'getCreateEventId', 'getBeanFromArgs', 'getFirstCreateId']
        );
        $apiMock->method('getAuditFields')->willReturn(['name', 'desc']);
        $apiMock->method('getCreateEventId')->willReturn('id1');
        $args = ['record' => 'r1'];
        $expected = [
            'Audit' => [
                [
                    'event_id' => [
                        '$not_equals' => 'id1',
                    ],
                ],
                [
                    'field_name' => [
                        '$in' => [
                            'name',
                            'desc',
                        ],
                    ],
                ],
            ],
        ];
        TestReflection::callProtectedMethod($apiMock, 'auditSetup', [$this->apiService, $args]);
        $result = TestReflection::getProtectedValue($apiMock, 'moduleFilters');
        $this->assertEquals($expected, $result);

        $beanMock = $this->createMock(SugarBean::class);
        $apiMock->method('getBeanFromArgs')->willReturn($beanMock);
        $apiMock->method('getFirstCreateId')->willReturn('id2');
        $args['add_create_record'] = '1';
        $expected['AuditCreate'] = [
            [
                'id' => [
                    '$equals' => 'id2',
                ],
            ],
        ];
        TestReflection::setProtectedValue($apiMock, 'moduleFilters', []);
        TestReflection::callProtectedMethod($apiMock, 'auditSetup', [$this->apiService, $args]);
        $result = TestReflection::getProtectedValue($apiMock, 'moduleFilters');
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getEnabledModules
     */
    public function testGetEnabledModules()
    {
        $apiMock = $this->createPartialMock(
            \RelatedActivitiesApi::class,
            ['getTimelineSettings', 'getDefaultModuleList']
        );
        $apiMock->method('getTimelineSettings')->willReturn([]);
        $apiMock->method('getDefaultModuleList')->willReturn([
            'message_invites' => 'Messages',
        ]);
        $args = ['module' => 'Contacts'];
        TestReflection::callProtectedMethod($apiMock, 'getEnabledModules', [$this->apiService, $args]);
        $result = TestReflection::getProtectedValue($apiMock, 'moduleList');
        $this->assertEquals('Messages', $result['message_invites']);
    }

    /**
     * @covers ::formatAuditRecord
     */
    public function testFormatAuditRecord()
    {
        $apiMock = $this->createPartialMock(
            \RelatedActivitiesApi::class,
            ['getBeanFromArgs']
        );
        TestReflection::setProtectedValue($apiMock, 'createEventId', 'create_event_id');

        $args = [
            'module' => 'Account',
            'record' => '1',
        ];

        $focusMock = $this->createMock(SugarBean::class);
        $focusMock->created_by_name = 'name1';
        $focusMock->created_by = 'id1';

        $apiMock->method('getBeanFromArgs')->willReturn($focusMock);

        $record = [
            'event_id' => 'create_event_id',
        ];

        $result = TestReflection::callProtectedMethod($apiMock, 'formatAuditRecord', [$args, $record]);

        $this->assertEquals($focusMock->created_by_name, $result['created_by_name']);
        $this->assertEquals($focusMock->created_by, $result['created_by']);
    }
}
