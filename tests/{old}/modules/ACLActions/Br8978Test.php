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
 * @coversDefaultClass ACLAction
 */
class Br8978Test extends TestCase
{
    /**
     * @covers ::hasAccess
     * @return void
     */
    public function testHasAccessDynamicCall()
    {
        $mock = new ACLActionBr8978Mock();
        $mock->aclaccess = ACL_ALLOW_ALL;
        $this->assertFalse($mock->hasAccess(false, 0));
    }
}

class ACLActionBr8978Mock extends ACLAction
{
    public function __construct()
    {
    }
}
