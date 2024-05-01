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

class Bug41893Test extends TestCase
{
    private $created_anonymous_user = false;

    protected function setUp(): void
    {
        if (!isset($GLOBALS['current_user'])) {
            $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
            $this->created_anonymous_user = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->created_anonymous_user) {
            SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
            unset($GLOBALS['current_user']);
        }
    }


    public function testFieldsVisibilityToStudioListView()
    {
        $task = new Task();
        $this->assertFalse($task->field_defs['contact_email']['studio'], 'Assert contact_email is hidden in studio');
        $this->assertTrue($task->field_defs['contact_phone']['studio']['listview'], 'Assert contact_phone is visible in studio for listview');
    }
}
