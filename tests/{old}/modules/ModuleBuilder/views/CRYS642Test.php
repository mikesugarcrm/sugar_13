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

class CRYS642Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testNoEmptyValuesReturned()
    {
        $relationships = new DeployedRelationships('Accounts');
        $view = new ViewRelationships();
        $allData = $view->getAjaxRelationships($relationships);

        $noLabelValuesArray = array_filter($allData, function ($item) {
            return !empty($item['rhs_module']);
        });

        $this->assertSameSize($allData, $noLabelValuesArray);
    }
}
