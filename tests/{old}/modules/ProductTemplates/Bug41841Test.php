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

class Bug41841Test extends TestCase
{
    public function testManufacturerNameStudioProperty()
    {
        $dictionary = [];
        require 'modules/ProductTemplates/vardefs.php';
        $manufacturer_name = [];
        $manufacturer_name = $dictionary['ProductTemplate']['fields']['manufacturer_name'];
        /*first checking the existence of 'studio' array element for manufacturer_name field and then checking if it is set to 'false'*/
        $this->assertFalse(empty($manufacturer_name['studio']), 'array element studio should be present for manufacturer name');
        $this->assertEquals('false', $manufacturer_name['studio']);
    }
}
