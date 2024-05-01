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
class Bug33906Test extends TestCase
{
    private $folder = null;
    private $user = null;

    protected function setUp(): void
    {
        global $current_user, $currentModule;

        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->user;
        $this->folder = new SugarFolder();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->user->id}'");
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$this->folder->id}'");
        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$this->folder->id}'");

        unset($this->folder);
    }

    public function testSaveFolderNoSubscriptions()
    {
        global $current_user;
        $this->folder->save();

        $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$this->folder->id}'");
        $rs = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertGreaterThan(0, $rs['cnt'], 'Could not create folder subscriptions properly.');
    }

    public function testSaveFolderWithSubscriptions()
    {
        global $current_user;
        $this->folder->save(false);

        $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$this->folder->id}'");
        $rs = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals(0, $rs['cnt'], 'Created folder subscriptions when none should have been created.');
    }
}
