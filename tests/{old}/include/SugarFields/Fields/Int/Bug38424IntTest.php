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

class Bug38424IntTest extends TestCase
{
    private $fieldOutput;

    protected function setUp(): void
    {
        $sfr = new SugarFieldInt('int');
        $vardef = [
            'name' => 'bug_38424_int_test',
            'len' => '10',
        ];
        $this->fieldOutput = $sfr->getEditViewSmarty('', $vardef, [], 1);
    }

    public function testMaxLength()
    {
        $this->assertStringContainsString("maxlength='10'", $this->fieldOutput);
    }
}
