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

class Bug57208Test extends TestCase
{
    private $testModule = 'Bug57208Test';

    protected function setUp(): void
    {
        sugar_mkdir("modules/{$this->testModule}");
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
    }

    protected function tearDown(): void
    {
        rmdir("modules/{$this->testModule}");
        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug57208
     */
    public function testModuleTypeIsBasicForModuleWithNoBeanListEntry()
    {
        $sm = new StudioModule($this->testModule);
        $type = $sm->getType();

        $this->assertEquals('basic', $type, "Type should be 'basic' but '$type' was returned from StudioModule :: getType()");
    }
}
