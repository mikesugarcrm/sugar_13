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

class One2MRelationshipTest extends TestCase
{
    /**
     * @covers One2MRelationship::getType
     */
    public function testGetType()
    {
        $relationship = $this->getMockBuilder('One2MRelationship')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(REL_TYPE_MANY, $relationship->getType(REL_LHS));
        $this->assertEquals(REL_TYPE_ONE, $relationship->getType(REL_RHS));
    }
}
