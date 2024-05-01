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
 * @group 54858
 */
class Bug54858Test extends TestCase
{
    /**
     * @var \User|mixed
     */
    public $user;
    /**
     * @var string
     */
    public $vcal_url;

    protected function setUp(): void
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->email1 = $email = 'test' . uniqid() . '@test.com';
        $this->user->save();
        $GLOBALS['current_user'] = $this->user;
        $this->vcal_url = "{$GLOBALS['sugar_config']['site_url']}/vcal_server.php/type=vfb&source=outlook&email=" . urlencode($email);
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Test that new user gets ical key
     */
    public function testCreateNewUser()
    {
        $this->assertNotEmpty($this->user->getPreference('calendar_publish_key'), 'Publish key is not set');
    }
}
