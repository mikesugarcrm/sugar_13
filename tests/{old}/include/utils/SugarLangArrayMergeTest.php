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

class SugarLangArrayMergeTest extends TestCase
{
    public function testSugarLangArrayMerge()
    {
        $target = [];
        $target['LBL_TO_BE_CHANGED'] = 'Good';
        $target['LBL_UNCHANGED'] = 'Blah';
        $target['LBL_UNCHANGED_TOO'] = 'foo';
        $target['LBL_DOM'] = ['LBL_ONE' => 'One', 'LBL_TWO' => 'Two', 'LBL_THREE' => 'Three'];

        $source = [];
        $source['LBL_TO_BE_CHANGED'] = 'Better';
        $source['LBL_UNCHANGED_TOO'] = '';
        $source['LBL_DOM'] = ['LBL_ONE' => '', 'LBL_TWO' => 'Deux', 'LBL_FOUR' => 'Quatre'];

        $merged = sugarLangArrayMerge($target, $source);
        $this->assertEquals(
            'Better',
            $merged['LBL_TO_BE_CHANGED'],
            'Source string should have overwritten target string.'
        );
        $this->assertEquals('Blah', $merged['LBL_UNCHANGED'], 'Source string should not have been changed.');
        $this->assertEquals(
            'foo',
            $merged['LBL_UNCHANGED_TOO'],
            'Source string should not have been changed to empty string.'
        );
        $this->assertEquals(
            ['LBL_ONE' => 'One', 'LBL_TWO' => 'Deux', 'LBL_THREE' => 'Three', 'LBL_FOUR' => 'Quatre'],
            $merged['LBL_DOM'],
            'Should merge subarrays too'
        );
        $this->assertSameSize($target, $merged);
    }
}
