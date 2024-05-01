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
 * @coversDefaultClass CasesApiHelper
 */
class CasesApiHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION['type'] = 'support_portal';
    }

    protected function tearDown(): void
    {
        unset($_SESSION['type']);
    }

    /**
     * @covers ::populateFromApi()
     */
    public function testPopulateFromApi()
    {
        $helper = new CasesApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $bean = BeanFactory::newBean('Cases');
        $helper->populateFromApi($bean, ['name' => 'some name']);
        $this->assertEquals('Portal', $bean->source);
    }
}
