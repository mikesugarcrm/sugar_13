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
use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;

/**
 * @coversDefaultClass CasesFilterApi
 */
class CasesFilterApiTest extends TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var CasesFilterApi */
    protected $api = null;

    /** @var Account */
    protected $account = null;

    /** @var Contact */
    protected $contact = null;

    /** @var aCase */
    protected $case = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->service = SugarTestRestUtilities::getRestServiceMock();

        $this->api = new CasesFilterApi();

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->load_relationship('contacts');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->account_id = $this->account->id;
        $this->contact->assigned_user_id = '1';
        $this->contact->save();
        $this->account->contacts->add($this->contact);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['type'], $_SESSION['contact_id']);
        PortalFactory::getInstance('Session')->unsetCache();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::getContactCases
     */
    public function testGetContactCases()
    {
        // not accessible
        $this->case = SugarTestCaseUtilities::createCase();
        $data = $this->api->getContactCases($this->service, [
            'module' => 'Cases',
            'contact_id' => $this->contact->id,
            'fields' => 'id',
            'filter' => [
                ['case_number' => $this->case->case_number],
            ],
        ]);
        $this->assertEmpty($data['records']);

        // accessible
        $this->case->account_id = $this->account->id;
        $this->case->save();
        $data = $this->api->getContactCases($this->service, [
            'module' => 'Cases',
            'contact_id' => $this->contact->id,
            'fields' => 'id',
            'filter' => [
                ['case_number' => $this->case->case_number],
            ],
        ]);
        $this->assertNotEmpty($data['records']);
        $this->assertArrayHasKey('id', $data['records'][0]);
        $this->assertEquals($this->case->id, $data['records'][0]['id']);
    }
}
