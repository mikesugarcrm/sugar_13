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

require_once 'modules/OAuthTokens/OAuthToken.php';

class OAuthTokenTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        OAuthToken::deleteByUser($GLOBALS['current_user']->id);
        SugarTestHelper::tearDown();
    }

    /**
     * @ticket 62822
     */
    public function testCleanup()
    {
        // create request token
        $tok = OAuthToken::generate();
        $tok->setState(OAuthToken::REQUEST);
        $tok->assigned_user_id = $GLOBALS['current_user']->id;
        $tok->save();
        // create invalid token
        $tok = OAuthToken::generate();
        $tok->setState(OAuthToken::INVALID);
        $tok->assigned_user_id = $GLOBALS['current_user']->id;
        $tok->save();

        $cnt = $GLOBALS['db']->getOne("SELECT count(*) c FROM {$tok->table_name} WHERE assigned_user_id=" . $GLOBALS['db']->quoted($GLOBALS['current_user']->id));
        $this->assertEquals(2, $cnt, 'Wrong number of tokens in the table');

        // set time way in the past
        $GLOBALS['db']->query("UPDATE {$tok->table_name} SET token_ts=1 WHERE assigned_user_id=" . $GLOBALS['db']->quoted($GLOBALS['current_user']->id));

        // run cleanup
        OAuthToken::cleanup();

        // ensure tokens are gone
        $cnt = $GLOBALS['db']->getOne("SELECT count(*) c FROM {$tok->table_name} WHERE assigned_user_id=" . $GLOBALS['db']->quoted($GLOBALS['current_user']->id));
        $this->assertEquals(0, $cnt, 'Tokens were not deleted');
    }
}
