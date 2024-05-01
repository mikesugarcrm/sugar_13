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

class PMSEElementExceptionTest extends TestCase
{
    public function testToString()
    {
        $message = 'Exception message';
        $flowData = ['sample_flow_data'];
        $element = new stdClass();
        $code = 1;
        $exception = new PMSEElementException($message, $flowData, $element, $code);
        $result = $exception->__toString();
        $this->assertEquals("PMSEElementException: [1]: Exception message\n", $result);
        $this->assertEquals($element, $exception->getElement());
        $this->assertEquals([0 => 'sample_flow_data', 'cas_flow_status' => 'ERROR'], $exception->getFlowData());
    }
}
