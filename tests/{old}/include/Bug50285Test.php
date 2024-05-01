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

class Bug50285Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
    }

    // This will fail without fix for Bug50285
    public function testGetImageFunctionWithMinimalParameters()
    {
        try {
            $this->assertNotNull(get_image('select', ''));
        } catch (Exception $e) {
            $this->fail('Call to get_image function with minimal parameters causes exception:  ' . $e->getMessage());
        }
    }

    // This will fail without fix for Bug50285
    public function testGetImageFunctionWithSomeParameters()
    {
        try {
            $this->assertNotNull(get_image('select', '', null, null, '.gif'));
        } catch (Exception $e) {
            $this->fail('Call to get_image function without full parameters causes exception:  ' . $e->getMessage());
        }
    }

    // This should always pass
    public function testGetImageFunctionWithAllParameters()
    {
        try {
            $this->assertNotNull(get_image('select', '', null, null, '.gif', 'test alt text'));
        } catch (Exception $e) {
            $this->fail('Call to get_image function with all parameters causes exception:  ' . $e->getMessage());
        }
    }
}
