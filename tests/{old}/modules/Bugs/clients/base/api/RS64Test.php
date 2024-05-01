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
 * RS-64: Prepare Bugs Api
 */
class RS64Test extends TestCase
{
    /** @var Contact */
    protected $account = null;

    /** @var Contact */
    protected $contact = null;

    /** @var Bug */
    protected $bug = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $user = SugarTestHelper::setUp('current_user', [true, true]);

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->load_relationship('contacts');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->account_id = $this->account->id;
        $this->contact->assigned_user_id = $user->id;
        $this->contact->team_id = 1;
        $this->contact->team_set_id = 1;
        $this->contact->save();
        $this->account->contacts->add($this->contact);


        $_SESSION['type'] = 'support_portal';
        $_SESSION['contact_id'] = $this->contact->id;
    }

    protected function tearDown(): void
    {
        unset($_SESSION['type'], $_SESSION['contact_id']);
        if ($this->bug instanceof Bug) {
            $this->bug->mark_deleted($this->bug->id);
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of createRecord method
     */
    public function testCreateRecord()
    {
        $service = SugarTestRestUtilities::getRestServiceMock();

        $api = new ModulePortalApi();
        $data = $api->createRecord($service, [
            'module' => 'Bugs',
            'name' => 'Test Bug',
            'assigned_user_id' => 1,
            'team_id' => 2,
            'team_set_id' => 2,
        ]);
        $this->assertArrayHasKey('id', $data);

        $this->bug = BeanFactory::getBean('Bugs', $data['id']);
        $this->assertEquals($this->contact->assigned_user_id, $this->bug->assigned_user_id);
        $this->assertEquals($this->contact->team_id, $this->bug->team_id);
        $this->assertEquals($this->contact->team_set_id, $this->bug->team_set_id);

        $this->bug->load_relationship('contacts');
        $this->bug->load_relationship('accounts');
        $contacts = $this->bug->contacts->getBeans();
        $this->assertArrayHasKey($this->contact->id, $contacts);
        $accounts = $this->bug->accounts->getBeans();
        $this->assertArrayHasKey($this->account->id, $accounts);
    }
}
