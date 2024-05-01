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


class RestUpdateTest extends RestTestBase
{
    /**
     * @var mixed|\Account
     */
    public $account;
    /**
     * @var mixed|\Contact
     */
    public $contact;
    /**
     * @var mixed|\Note
     */
    public $note;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (isset($this->account->id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->account->id}'");
            }
        }
        if (isset($this->contact->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c = '{$this->contact->id}'");
            }
        }
        if (isset($this->note->id)) {
            $GLOBALS['db']->query("DELETE FROM notes WHERE id = '{$this->note->id}'");
        }

        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '" . $GLOBALS['current_user']->id . "'");

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testUpdate()
    {
        $this->account = new Account();
        $this->account->name = 'UNIT TEST - BEFORE';
        $this->account->save();

        $GLOBALS['db']->commit();

        $restReply = $this->restCall("Accounts/{$this->account->id}", json_encode(['name' => 'UNIT TEST - AFTER']), 'PUT');

        $this->assertEquals($this->account->id, $restReply['reply']['id'], 'The returned account id was not the same.');

        $account2 = new Account();
        $account2->retrieve($this->account->id);

        $this->assertEquals(
            'UNIT TEST - AFTER',
            $account2->name,
            'Did not set the account name.'
        );

        $this->assertEquals(
            $restReply['reply']['name'],
            $account2->name,
            'Rest Reply and Bean Do Not Match.'
        );
    }

    /**
     * @group rest
     */
    public function testSetFavorite()
    {
        $this->account = new Account();
        $this->account->name = 'UNIT TEST - BEFORE';
        $this->account->save();

        $GLOBALS['db']->commit();


        $restReply = $this->restCall("Accounts/{$this->account->id}", json_encode(['my_favorite' => true]), 'PUT');

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->user->id);

        $this->assertEquals($is_fav, (bool)$restReply['reply']['my_favorite'], 'The returned favorite was not the same.');
    }

    /**
     * @group rest
     */
    public function testRemoveFavorite()
    {
        $this->account = new Account();
        $this->account->name = 'UNIT TEST - BEFORE';
        $this->account->save();

        $GLOBALS['db']->commit();

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Accounts', $this->account->id);
        $fav->new_with_id = true;
        $fav->module = 'Accounts';
        $fav->record_id = $this->account->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $GLOBALS['db']->commit();

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->user->id);

        $this->assertEquals($is_fav, true, "Didn't actually set the favorite");

        $restReply = $this->restCall("Accounts/{$this->account->id}", json_encode(['my_favorite' => false]), 'PUT');

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->user->id);

        $this->assertEquals($is_fav, (bool)$restReply['reply']['my_favorite'], 'The returned favorite was not the same.');
    }

    /**
     * @group rest
     */
    public function testDeleteFavorite()
    {
        $this->account = new Account();
        $this->account->name = 'UNIT TEST - BEFORE';
        $this->account->save();

        $GLOBALS['db']->commit();

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Accounts', $this->account->id);
        $fav->new_with_id = true;
        $fav->module = 'Accounts';
        $fav->record_id = $this->account->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $GLOBALS['db']->commit();

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->user->id);

        $this->assertEquals($is_fav, true, "Didn't actually set the favorite");

        $restReply = $this->restCall("Accounts/{$this->account->id}/favorite", [], 'DELETE');

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->user->id);

        $this->assertEquals($is_fav, (bool)$restReply['reply']['my_favorite'], 'The returned favorite was not the same.');
    }

    /**
     * @group rest
     */
    public function testUpdateEmail()
    {
        $this->contact = new Contact();
        $this->contact->first_name = 'UNIT TEST - BEFORE';
        $this->contact->save();

        $GLOBALS['db']->commit();


        $emails = [
            [
                'email_address' => 'test@test.com',
                'opt_out' => '0',
                'invalid_email' => '0',
                'primary_address' => '1',
            ],
            [
                'email_address' => 'asdf@test.com',
                'opt_out' => '0',
                'invalid_email' => '1',
                'primary_address' => '0',
            ],
        ];
        $restReply = $this->restCall("Contacts/{$this->contact->id}", json_encode([
            'first_name' => 'UNIT TEST - AFTER',
            'email' => $emails,
        ]), 'PUT');

        $this->assertEquals($this->contact->id, $restReply['reply']['id'], 'The returned contact id was not the same.');

        $contact2 = new Contact();
        $contact2->retrieve($this->contact->id);
        $restReply = $this->restCall("Contacts/{$this->contact->id}");

        $this->assertEquals($restReply['reply']['email'], $emails, "Returned emails don't match");

        $this->assertEquals(
            'UNIT TEST - AFTER',
            $contact2->name,
            'Did not set the contact name.'
        );

        $this->assertEquals(
            $restReply['reply']['name'],
            $contact2->name,
            'Rest Reply and Bean Do Not Match.'
        );
    }

    /**
     * @group rest
     */
    public function testHasParentNameAfterSave()
    {
        // Build an account
        $this->account = new Account();
        $this->account->name = 'ABC TEST';
        $this->account->save();

        // Build a Note with a parent id of the account
        $this->note = new Note();
        $this->note->name = 'UNIT TEST Note';
        $this->note->parent_id = $this->account->id;
        $this->note->parent_type = 'Accounts';
        $this->note->description = 'Some description';
        $this->note->save();

        $GLOBALS['db']->commit();

        // Change the note description and check for parent_name
        $reply = $this->restCall("Notes/{$this->note->id}", json_encode(['description' => 'Some other descriptions']), 'PUT');
        $this->assertEquals($this->note->id, $reply['reply']['id'], 'Note ID was not the correct ID');
        $this->assertEquals($this->account->name, $reply['reply']['parent_name'], 'Parent Account name was not returned or was incorrect');
    }
}
