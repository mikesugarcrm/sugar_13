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

class Bug50338Test extends TestCase
{
    private $TemplateHandler;
    private $fieldDefs;

    public function testCreateFieldFefs()
    {
        $this->TemplateHandler = new MockTemplateHandler();
        $this->fieldDefs = [
            'amount' => [
                'calculated' => true,
                'formula' => 'add($calc1_c, $calc2_c)',
            ],
            'calc1_c' => [
                'id' => 'Opportunitiescalc1_c',
            ],
            'calc2_c' => [
                'id' => 'Opportunitiescalc2_c',
            ],
        ];
        $fieldDefs = $this->TemplateHandler->mockPrepareCalculationFields($this->fieldDefs, 'Opportunities');
        $this->assertArrayHasKey('Opportunitiesamount', $fieldDefs);
        $this->assertStringContainsString('Opportunitiescalc1_c', $fieldDefs['Opportunitiesamount']['formula']);
        $this->assertStringContainsString('Opportunitiescalc2_c', $fieldDefs['Opportunitiesamount']['formula']);
    }
}

class MockTemplateHandler extends TemplateHandler
{
    public function mockPrepareCalculationFields($fieldDefs, $module)
    {
        return $this->prepareCalculationFields($fieldDefs, $module);
    }
}
