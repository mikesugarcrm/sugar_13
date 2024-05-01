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

namespace Sugarcrm\SugarcrmTestsUnit\modules\PdfManager;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PdfManagerHooks
 */
class PdfManagerHooksTest extends TestCase
{
    protected function setUp(): void
    {
        \SugarAutoLoader::load('../../modules/PdfManager/PdfManagerHooks.php');
        \SugarAutoLoader::load('../../modules/PdfManager/PdfManager.php');
    }

    /**
     * @covers ::fixAmp
     *
     * @return void
     */
    public function testFixAmp()
    {
        $pdfManagerMock = $this->getMockBuilder('\PdfManager')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfManagerHooksMock = $this->getMockBuilder('\PdfManagerHooks')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $pdfManagerMock->body_html = 'foo&amp;bar';

        $pdfManagerHooksMock->fixAmp($pdfManagerMock, 'before_save');
        $this->assertEquals('foo&bar', $pdfManagerMock->body_html);
    }
}
