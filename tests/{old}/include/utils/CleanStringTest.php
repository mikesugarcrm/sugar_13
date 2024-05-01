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

require_once 'include/utils.php';

class CleanStringTest extends TestCase
{
    public function providerCleanString()
    {
        return [
            ['4.2.1', 'STANDARD', true],
            ['4.2$1', 'STANDARD', false],
            ['abc', 'NUMBER', false],
            ['date(d)', 'SQL_COLUMN_LIST', true],
        ];
    }

    /**
     * @dataProvider providerCleanString
     * @ticket 45877
     */
    public function testCleanString(
        $string,
        $filter,
        $resultBool
    ) {

        if ($resultBool) {
            $this->assertEquals($string, clean_string($string, $filter, false));
        } else {
            $this->assertFalse(clean_string($string, $filter, false));
        }
    }
}
