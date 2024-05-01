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
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @group ApiTests
 */
class RelatedActivitiesApiTest extends TestCase
{
    /**
     * @var \aCase|mixed
     */
    public $case;
    /**
     * @var RelatedActivitiesApi
     */
    protected $filterApi = null;

    /**
     * @var RestService
     */
    protected $serviceMock = null;

    /**
     * @var Note
     */
    protected $note = null;

    /**
     * @var Call
     */
    protected $call = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->filterApi = new RelatedActivitiesApi();
        TestReflection::setProtectedValue($this->filterApi, 'validFields', ['date_modified']);
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        // parent module
        $this->case = SugarTestCaseUtilities::createCase();

        // related module
        $this->call = SugarTestCallUtilities::createCall();
        $this->call->parent_type = 'Cases';
        $this->call->parent_id = $this->case->id;
        $this->call->status = 'Planned';
        $this->call->save();
    }

    protected function tearDown(): void
    {
        unset($this->filterApi);
        unset($this->serviceMock);
        unset($this->call);
        unset($this->case);

        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
    }

    /**
     * @throws SugarApiExceptionNotAuthorized
     */
    public function testGetRelatedActivities()
    {
        $return = $this->filterApi->getRelatedActivities(
            $this->serviceMock,
            [
                'module' => 'Cases',
                'record' => $this->case->id,
                'module_list' => 'Calls,Meetings',
                'max_num' => 5,
            ],
            'list'
        );

        $this->assertIsArray($return);
        $this->assertNotEmpty($return['next_offset']);
        $this->assertIsArray($return['records']);

        // The first and only record should be the related Call we created
        $this->assertCount(1, $return['records']);
        $record = $return['records'][0];
        $this->assertSame($this->call->id, $record['id']);
        $this->assertSame('Calls', $record['_module']);
    }
}
