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

class Bug50171Test extends TestCase
{
    public function testGetJSMock()
    {
        global $mod_strings;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Import');
        $mock = new Bug50171ImportViewStep3Mock();
        $required = ["It's A Bug!"];
        $output = $mock->getJS($required);
        $this->assertMatchesRegularExpression('/required\[\'0\'\] = \'It\&\#039\;s A Bug\!/', $output);
    }
}


class Bug50171ImportViewStep3Mock extends ImportViewStep3
{
    public function getJS($required)
    {
        return $this->_getJS($required);
    }
}
