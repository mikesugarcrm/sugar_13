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

require_once 'modules/Currencies/Currency.php';

/**
 * Bug #48690
 * retrieve function did not return data for default currency
 *
 * @author mgusev@sugarcrm.com
 * @ticket 48690
 */
class Bug48690Test extends TestCase
{
    /**
     * Test checks what method retrieve returns
     * @group 48690
     */
    public function testRetrieve()
    {
        $currency = new Currency();
        $actual = $currency->retrieve('-99');
        $this->assertNotNull($actual, 'Currency object is not returned');
    }
}
