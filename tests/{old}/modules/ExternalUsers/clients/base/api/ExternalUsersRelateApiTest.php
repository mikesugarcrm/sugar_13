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
 * @coversDefaultClass ExternalUsersRelateApi
 */
class ExternalUsersRelateApiTest extends TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var ExternalUsersRelateApi */
    protected $api = null;

    /** @var ExternalUser */
    protected $extUser = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new ExternalUsersRelateApi();

        $this->extUser = SugarTestExternalUserUtilities::createExternalUser();
        $this->extUser->external_id = 'abcde';
        $this->extUser->save();
        $this->extUser->load_relationship('cases');

        $case = SugarTestCaseUtilities::createCase();
        $case->description = 'related';
        $case->save();
        $this->extUser->cases->add($case);

        $case = SugarTestCaseUtilities::createCase();
        $case->description = 'external';
        $case->source_id = 'abcde';
        $case->save();
    }

    protected function tearDown(): void
    {
        SugarTestExternalUserUtilities::removeAllCreatedExternalUsers();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::filterRelatedSetup
     */
    public function testFilterRelatedSetup()
    {
        $data = $this->api->filterRelated($this->service, [
            'module' => 'ExternalUsers',
            'record' => $this->extUser->id,
            'link_name' => 'cases',
            'fields' => 'id,description',
            'include_external_items' => 'true',
            'order_by' => 'description',
        ]);
        $this->assertEquals(safeCount($data['records']), 2);
        $this->assertEquals($data['records'][0]['description'], 'external');
        $this->assertEquals($data['records'][1]['description'], 'related');
    }
}
