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

class Bug50887Test extends TestCase
{
    protected $user;
    protected $loc;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->user = $GLOBALS['current_user'];

        $this->user->setPreference('default_decimal_seperator', '.');
        $this->loc = Localization::getObject();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
    }

    public function testGetDecimalSeparator()
    {
        $this->assertSame('.', $this->loc->getDecimalSeparator($this->user));
    }
}
