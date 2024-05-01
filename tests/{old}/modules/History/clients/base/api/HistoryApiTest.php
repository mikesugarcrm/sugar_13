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

/**
 * @group ApiTests
 */
class HistoryApiTest extends TestCase
{
    /** @var HistoryApi */
    protected $filterApi = null;

    /** @var RestService */
    protected $serviceMock = null;

    /** @var Account */
    protected $account = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->filterApi = new HistoryApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $this->account = SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown(): void
    {
        unset($this->filterApi);
        unset($this->serviceMock);
        unset($this->account);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testFilterSetup()
    {
        $return = $this->filterApi->filterModuleList(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'record' => $this->account->id,
                'module_list' => 'Calls,Emails,Meetings,Notes,Tasks',
                'max_num' => 20,
            ],
            'list'
        );
        $this->assertNotEmpty($return, 'HistoryAPI is broken');
    }

    /**
     * @dataProvider scrubFieldsProvider
     * @param array $aliasFields
     * @param array|string $expected
     */
    public function testScrubFields(array $aliasFields, $expected)
    {
        $whiteList = [
            'date_entered' => true,
        ];
        $args = [['fields' => 'date_entered', 'alias_fields' => $aliasFields, 'order_by' => []], $whiteList];
        if (is_string($expected)) {
            $this->expectException($expected);
        }
        $args = SugarTestReflection::callProtectedMethod($this->filterApi, 'scrubFields', $args);
        if (is_array($expected)) {
            $this->assertEquals($expected, $args['alias_fields']);
        }
    }

    public function scrubFieldsProvider()
    {
        $validName = $GLOBALS['db']->getValidDBName('drop table', true, 'column', true);
        return [
            [
                [
                    'record_date' => [
                        'BadModule' => 'some_field',
                    ],
                ],
                SugarApiExceptionInvalidParameter::class,
            ],
            [
                [
                    'record_date' => [
                        'Calls' => 'bad_field',
                    ],
                ],
                SugarApiExceptionInvalidParameter::class,
            ],
            [
                [
                    // alias contains illegal words
                    'drop table' => [
                        'Calls' => 'date_entered',
                    ],
                ],
                [
                    $validName => [
                        'Calls' => 'date_entered',
                    ],
                ],
            ],
        ];
    }
}
