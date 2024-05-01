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

class Bug58307Test extends TestCase
{
    private $fv;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        // Setting the module in the request for this test
        $_REQUEST['view_module'] = 'Accounts';

        $this->fv = new FieldViewer();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testPhoneFieldGetsCorrectFieldForm()
    {
        $vardef = [
            'type' => 'phone',
            'len' => 30,
        ];

        $layout = $this->fv->getLayout($vardef);

        // Inspect the layout for things we expect. Yes, this is kinda not
        // scientific but to support varies builds this needs to happen this way.
        $this->assertStringContainsString('function forceRange(', $layout);
        $this->assertStringContainsString(
            "<input type='text' name='default' id='default' value='' maxlength='30'>",
            $layout
        );
    }
}
