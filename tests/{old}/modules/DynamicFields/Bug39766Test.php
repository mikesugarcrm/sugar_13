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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;

require_once 'modules/DynamicFields/FieldCases.php';


class Bug39766Test extends TestCase
{
    /**
     * @group bug35265
     */
    public function testFloatPrecisionMapping()
    {
        $request = InputValidation::create([
            'precision' => 2,
        ], []);

        require_once 'modules/DynamicFields/FieldCases.php';
        $field = get_widget('float');
        $field->populateFromPost($request);

        $this->assertEquals($field->ext1, 2, 'Asserting that the ext1 value was set to the proper precision');
        $this->assertEquals($field->precision, 2, 'Asserting that the precision value was set to the proper precision');
    }
}
