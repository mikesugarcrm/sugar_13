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

require_once 'modules/ProductTemplates/Formulas.php';

class Bug44515WithoutCustomTest extends TestCase
{
    /**
     * @group 44515
     */
    public function testLoadCustomFormulas()
    {
        refresh_price_formulas();
        // At this point I expect to have only the 5 standard formulas
        $expectedIndexes = 5;
        $this->assertEquals($expectedIndexes, safeCount($GLOBALS['price_formulas']));
    }
}
