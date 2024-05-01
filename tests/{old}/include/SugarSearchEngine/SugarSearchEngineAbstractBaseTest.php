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

class SugarSearchEngineAbstractBaseTest extends TestCase
{
    public function testSearchEngineDown()
    {
        SugarSearchEngineAbstractBase::markSearchEngineStatus(true);
        $isDown = SugarSearchEngineAbstractBase::isSearchEngineDown();
        $this->assertTrue($isDown, 'Incorrect status');
    }

    public function testSearchEngineUp()
    {
        SugarSearchEngineAbstractBase::markSearchEngineStatus(false);
        $isDown = SugarSearchEngineAbstractBase::isSearchEngineDown();
        $this->assertFalse($isDown, 'Incorrect status');
    }
}
