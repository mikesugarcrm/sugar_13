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
 * Bug49873Test.php
 * This test is to check the removal of the Contracts relationships in a Document instance.  We test the mark_deleted call
 * on the Document instance to assert that the related Contracts are marked as deleted as well.  Previously, there was no logic
 * to cleanup related Contracts.
 *
 * @author Collin Lee
 */
class Bug49873Test extends TestCase
{
    public $doc = null;
    public $contract = null;

    protected function setUp(): void
    {
        global $current_user, $currentModule, $beanFiles, $beanList;
        include 'include/modules.php';
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Documents');
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->is_admin = 1;
        $current_user->save();
        $this->doc = new Document();
        $this->doc->document_name = 'Bug 49873 Test Document';
        $this->doc->assigned_user_id = $current_user->id;
        $this->doc->save();
        $this->contract = new Contract();
        $this->contract->name = 'Bug 49837 Test Contract';
        $this->contract->assigned_user_id = $current_user->id;
        $this->contract->save();
        $this->doc->load_relationship('contracts');
        $this->doc->contracts->add($this->contract->id);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM linked_documents WHERE document_id = '{$this->doc->id}'");
        $GLOBALS['db']->query("DELETE FROM documents WHERE id = '{$this->doc->id}'");
        unset($this->doc);
        $GLOBALS['db']->query("DELETE FROM contracts WHERE id = '{$this->contract->id}'");
        unset($this->contract);
    }

    /**
     * testDocumentMarkDeleted
     * This test will test the call to the mark_deleted function.  This should result in the mark_relationships_deleted function
     * being called in the Documents module.  Then we check that the contracts relationships have been appropriated marked.
     */
    public function testDocumentMarkDeleted()
    {
        $deleted = null;
        //Call mark_deleted
        $this->doc->mark_deleted($this->doc->id);
        $this->doc->save();

        //Now assert that the linked_documents entry (this holds the many-to-many documents to contracts relationship) is marked as deleted
        $results = $GLOBALS['db']->query("SELECT deleted FROM linked_documents WHERE document_id = '{$this->doc->id}'");
        while ($row = $GLOBALS['db']->fetchByAssoc($results)) {
            $deleted = $row['deleted'];
            break;
        }

        $this->assertEquals('1', $deleted, 'linked_documents entries are not deleted');
    }
}
