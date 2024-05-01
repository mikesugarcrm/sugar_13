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

class MetaDataManagerConnectionsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::init();
        SugarTestHelper::setUp('current_user', [true, 1]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function getModuleViewFieldsProvider(): array
    {
        return [
            'all fields' => [
                'Contacts',
                'full',
                array_keys(VardefManager::getFieldDefs('Contacts')),
            ],
            'all fields except links and collections' => [
                'Contacts',
                'detail',
                array_keys(
                    array_filter(
                        VardefManager::getFieldDefs('Contacts'),
                        function ($fieldDef) {
                            return !in_array($fieldDef['type'], ['collection', 'link']);
                        }
                    )
                ),
            ],
            'fields from base record view' => [
                'Contacts',
                'record',
                MetaDataManager::getManager()->getModuleViewFields('Contacts', 'record'),
            ],
            'call plus guests' => [
                'Calls',
                'detail',
                array_merge(
                    array_keys(
                        array_filter(
                            VardefManager::getFieldDefs('Calls'),
                            function ($fieldDef) {
                                return !in_array($fieldDef['type'], ['collection', 'link']);
                            }
                        )
                    ),
                    [
                        'invitees',
                    ],
                ),
            ],
            'meeting plus guests' => [
                'Meetings',
                'detail',
                array_merge(
                    array_keys(
                        array_filter(
                            VardefManager::getFieldDefs('Meetings'),
                            function ($fieldDef) {
                                return !in_array($fieldDef['type'], ['collection', 'link']);
                            }
                        )
                    ),
                    [
                        'invitees',
                    ],
                ),
            ],
        ];
    }

    public function retrieveRecordWithDetailViewProvider(): array
    {
        return [
            'calls detail view' => [
                function (): SugarBean {
                    $contact = SugarTestContactUtilities::createContact();
                    $call = SugarTestCallUtilities::createCall();
                    SugarTestCallUtilities::addCallContactRelation($call->id, $contact->id);

                    return $call;
                },
                new CallsApi(),
                // sample of expected fields in response
                [
                    'contacts',
                    'invitees',
                    'id',
                    'name',
                    'recurrence_id',
                    'team_name',
                ],
            ],
            'meetings detail view' => [
                function (): SugarBean {
                    $contact = SugarTestContactUtilities::createContact();
                    $meeting = SugarTestMeetingUtilities::createMeeting();
                    SugarTestMeetingUtilities::addMeetingContactRelation($meeting->id, $contact->id);

                    return $meeting;
                },
                new MeetingsApi(),
                // sample of expected fields in response
                [
                    'contacts',
                    'invitees',
                    'id',
                    'name',
                    'recurrence_id',
                    'team_name',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getModuleViewFieldsProvider
     * @param string $module The module name.
     * @param string $view The view name.
     * @param array $expectedFields The response must include these fields.
     */
    public function testGetModuleViewFields(string $module, string $view, array $expectedFields): void
    {
        $mm = MetaDataManager::getManager('connections');
        $fields = $mm->getModuleViewFields($module, $view);

        $this->assertEqualsCanonicalizing($expectedFields, $fields, 'wrong fields');
    }

    /**
     * @dataProvider retrieveRecordWithDetailViewProvider
     * @param callable $recordFactory Lazily creates the record to retrieve.
     *                                This is needed because data providers run
     *                                before everything else and the created
     *                                records may be deleted by other tests
     *                                before this test runs.
     * @param ModuleApi $api The API service to use.
     * @param array $fieldNames The response must include at least these fields.
     */
    public function testRetrieveRecordWithDetailView(callable $recordFactory, ModuleApi $api, array $fieldNames): void
    {
        // Create the data.
        $bean = $recordFactory();

        // Clear the cache to truly mimic an API request, which begins with an
        // empty cache. Otherwise the bean will be loaded from the cache and
        // some fields may not be set, which yields a different result than a
        // production API.
        BeanFactory::clearCache();

        $rest = SugarTestRestUtilities::getRestServiceMock($GLOBALS['current_user'], 'connections');
        $args = [
            'module' => $bean->getModuleName(),
            'record' => $bean->id,
            'view' => 'detail',
        ];
        $resp = $api->retrieveRecord($rest, $args);

        foreach ($fieldNames as $fieldName) {
            $this->assertArrayHasKey($fieldName, $resp, "response excludes '{$fieldName}'");
        }
    }
}
