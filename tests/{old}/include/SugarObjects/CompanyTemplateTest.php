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

class CompanyTemplateTest extends TestCase
{
    private $bean;

    protected function tearDown(): void
    {
        unset($this->bean);
    }

    public function testHasServiceLevelField()
    {
        $this->bean = BeanFactory::newBean('Accounts');
        $this->assertArrayHasKey('service_level', $this->bean->field_defs);

        $field = $this->bean->field_defs['service_level'];
        $this->assertEquals($field['name'], 'service_level');
        $this->assertEquals($field['type'], 'enum');
        $this->assertEquals($field['options'], 'service_level_dom');
    }
}
