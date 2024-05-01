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
 * @ticket 33404
 */
class AutoCreateImportFolderTest extends TestCase
{
    /**
     * @var \SugarFolder
     */
    public $folder;
    private $folder_id;
    private $ie;
    private $user;

    protected function setUp(): void
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->user;

        $this->folder = new SugarFolder();
        $this->ie = new InboundEmail();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$this->folder_id}'");

        unset($this->ie);
    }

    public function testAutoImportFolderCreation()
    {
        $this->ie->name = 'Sugar Test';
        $this->ie->team_id = create_guid();
        $this->ie->team_set_id = create_guid();
        $this->folder_id = $this->ie->createAutoImportSugarFolder();
        $folder_obj = new SugarFolder();
        $folder_obj->retrieve($this->folder_id);

        $this->assertEquals($this->ie->name, $folder_obj->name, 'Could not create folder for Inbound Email auto folder creation');
        $this->assertEquals($this->ie->team_id, $folder_obj->team_id, 'Could not create folder for Inbound Email auto folder creation');
        $this->assertEquals($this->ie->team_set_id, $folder_obj->team_set_id, 'Could not create folder for Inbound Email auto folder creation');
        $this->assertEquals(0, $folder_obj->has_child, 'Could not create folder for Inbound Email auto folder creation');
        $this->assertEquals(1, $folder_obj->is_group, 'Could not create folder for Inbound Email auto folder creation');
        $this->assertEquals($this->user->id, $folder_obj->assign_to_id, 'Could not create folder for Inbound Email auto folder creation');
    }
}
