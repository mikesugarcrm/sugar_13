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
 * Bug #66022x
 */
class Bug66022Test extends TestCase
{
    public static function dataProvider_processHTMLForWindowsMSOutlook()
    {
        $margin = "<style>p.MsoNormal {margin: 0;}</style>\n";
        return [
            [
                false,
                '<p class="MsoNormal">Row1</p><p> </p><p>aaa</p>',
                '<p class="MsoNormal">Row1</p><p> </p><p>aaa</p>',
            ],
            [
                /* Not processed - not seen as containing MSOutlook tags */
                true,
                '<p class="Mso">Row1</p><p> </p><p></p>',
                '<p class="Mso">Row1</p><p> </p><p></p>',
            ],
            [
                /* remove empty paragraph */
                true,
                '<p class="MsoNormal">Row1</p><p></p><p>aaa</p>',
                $margin . '<p class="MsoNormal">Row1</p><p>aaa</p>',
            ],
            [
                /* remove paragraph having single blank character - replace with <br/> */
                true,
                '<p class="MsoNormal">Row1</p><p> </p>',
                $margin . '<p class="MsoNormal">Row1</p><br/>',
            ],
            [
                /* remove paragraph having single non breaking space - replace with <br/> */
                true,
                '<p class="MsoNormal">Row1</p><p>&nbsp;</p>',
                $margin . '<p class="MsoNormal">Row1</p><br/>',
            ],
            [
                /* remove empty paragraph with class='MsoNormal'*/
                true,
                '<p class="MsoNormal">Row1</p><p class="MsoNormal"></p><p>aaa</p>',
                $margin . '<p class="MsoNormal">Row1</p><p>aaa</p>',
            ],
            [
                /* remove paragraph having single blank character with class='MsoNormal'*/
                true,
                '<p class="MsoNormal">Row1</p><p class="MsoNormal"> </p>',
                $margin . '<p class="MsoNormal">Row1</p>',
            ],
            [
                /* remove paragraph having single non breaking space with class='MsoNormal'*/
                true,
                '<p class="MsoNormal">Row1</p><p class="MsoNormal">&nbsp;</p>',
                $margin . '<p class="MsoNormal">Row1</p>',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_processHTMLForWindowsMSOutlook
     */
    public function testProcessHTMLForWindowsMSOutlook($setConfig, $htmlInput, $htmlExpected)
    {
        if ($setConfig) {
            $GLOBALS['sugar_config']['mso_fixup_paragraph_tags'] = true;
        } else {
            $GLOBALS['sugar_config']['mso_fixup_paragraph_tags'] = false;
        }
        $ie = new InboundEmail();
        $htmlResult = $ie->getHTMLDisplay($htmlInput);
        $this->assertEquals($htmlExpected, $htmlResult, 'MsoFixup result incorrect');
    }
}
