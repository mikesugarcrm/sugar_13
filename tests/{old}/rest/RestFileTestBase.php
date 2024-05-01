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


class RestFileTestBase extends RestTestBase
{
    protected $note;
    protected $note_id;
    protected $contact;
    protected $contact_id;
    protected $testfile1 = 'Bug55655-01.txt';
    protected $testfile2 = 'Bug55655-02.txt';

    protected function setUp(): void
    {
        parent::setUp();

        // Create two sample text files for uploading
        sugar_file_put_contents($this->testfile1, create_guid());
        sugar_file_put_contents($this->testfile2, create_guid());

        // Create a test contact and a test note
        $contact = new Contact();
        $contact->first_name = 'UNIT TEST';
        $contact->last_name = 'TESTY TEST';
        $contact->save();
        $this->contact_id = $contact->id;
        $this->contact = $contact;

        $note = new Note();
        $note->name = 'UNIT TEST';
        $note->description = 'UNIT TEST';
        $note->save();
        $this->note_id = $note->id;
        $this->note = $note;
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        unlink($this->testfile1);
        unlink($this->testfile2);

        parent::tearDown();

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->contact_id}'");
        $GLOBALS['db']->query("DELETE FROM notes WHERE id = '{$this->note_id}'");

        unset($this->contact, $this->note);
        $GLOBALS['db']->commit();
    }
}
