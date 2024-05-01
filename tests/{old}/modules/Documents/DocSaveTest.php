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

class DocSaveTest extends TestCase
{
    /**
     * Document instance to run tests with.
     * @var Document
     */
    public $doc = null;

    protected function setUp(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        $document = new Document();
        $document->name = 'Test Document';
        $document->save();
        $this->doc = $document;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM documents WHERE id = '{$this->doc->id}'");
        unset($this->doc);
    }

    public function testDocTypeSaveDefault()
    {
        // Assert doc type default is 'Sugar'
        $this->assertEquals($this->doc->doc_type, 'Sugar');
    }

    public function testDocTypeSaveDefaultInDb()
    {
        $query = "SELECT * FROM documents WHERE id = '{$this->doc->id}'";
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            // Assert doc type default is 'Sugar'
            $this->assertEquals($row['doc_type'], 'Sugar');
        }
    }
}
