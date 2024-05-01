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

class Bug44291Test extends TestCase
{
    public function testGetColumnType()
    {
        switch ($GLOBALS['db']->dbType) {
            case 'oci8':
                $this->assertEquals('number(26,6)', $GLOBALS['db']->getColumnType('currency'));
                break;
            default:
                $this->assertEquals('decimal(26,6)', $GLOBALS['db']->getColumnType('currency'));
        }
        $this->assertEquals('Unknown', $GLOBALS['db']->getColumnType('Unknown'));
    }
}
