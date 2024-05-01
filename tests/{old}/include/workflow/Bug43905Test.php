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
 * Bug #43905
 * Encrypted Value Is Displayed In Received Email Templates for Encrypt Fields
 * @ticket 43905
 */
class Bug43905Test extends TestCase
{
    /**
     * @group 43905
     * @return void
     */
    public function testEncrypt()
    {
        $myFieldSrc = 'FieldHaveToBeEncrypted';

        $object = new SugarBean();
        $object->parent_type = 'future_trigger';
        $object->rhs_value = $myFieldSrc;
        $object->exp_type = 'encrypt';
        $object->lhs_field = 'testField';
        $object->operator = 'Equals';

        $myFieldEnc = $object->encrpyt_before_save($myFieldSrc);

        $workFlowGlue = new WorkFlowGlue();
        $result = $workFlowGlue->glue_normal_expression($object);
        $this->assertStringContainsString($myFieldEnc, $result);
    }
}
