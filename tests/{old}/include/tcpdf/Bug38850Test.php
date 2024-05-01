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

require_once 'include/Sugarpdf/sugarpdf_config.php';
require_once 'vendor/tcpdf/config/lang/eng.php';
require_once 'vendor/tcpdf/tcpdf.php';

/**
 * @ticket 38850
 */
class Bug38850Test extends TestCase
{
    public function testCanInterjectCodeInTcpdfTag()
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $dom = [
            0 => [
                'value' => 'html',
            ],
            1 => [
                'parent' => 0,
                'value' => 'tcpdf',
                'attribute' => [
                    'method' => 'Close',
                    'params' => serialize([");echo ('Can Interject Code'"]),
                ],
            ],
        ];

        SugarTestReflection::callProtectedMethod($pdf, 'openHTMLTagHandler', [&$dom, 1]);

        $this->setOutputCallback(function ($output) {
            $this->assertStringNotContainsString('Can Interject Code', $output);
        });
    }
}
