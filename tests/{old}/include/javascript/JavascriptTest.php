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

class JavascriptTest extends TestCase
{
    private $javascript;

    protected function setUp(): void
    {
        $this->javascript = new javascript();
    }

    public function providerBuildStringToTranslateInSmarty()
    {
        return [
            [
                'LBL_TEST',
                "{/literal}{sugar_translate label='LBL_TEST' module='' for_js=true}{literal}",
            ],
            [
                ['LBL_TEST', 'LBL_TEST_2'],
                "{/literal}{sugar_translate label='LBL_TEST' module='' for_js=true}{literal}{/literal}{sugar_translate label='LBL_TEST_2' module='' for_js=true}{literal}",
            ],
        ];
    }

    /**
     * @dataProvider providerBuildStringToTranslateInSmarty
     * @ticket 41983
     */
    public function testBuildStringToTranslateInSmarty($string, $returnedString)
    {
        $this->assertEquals($returnedString, $this->javascript->buildStringToTranslateInSmarty($string));
    }
}
