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
 * @ticket 56259
 * @ticket PAT-580
 */
class Bug56259Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public function testUserACLs()
    {
        $result = $GLOBALS['current_user']->get_list('', '', 0, 100, -1, 0);
        foreach ($result['list'] as $bean) {
            if ($bean->id == $GLOBALS['current_user']->id) {
                continue;
            }
            $bean->ACLFilterFields();
            $this->assertEmpty($bean->user_hash, 'User hash not hidden');
        }
    }
}
