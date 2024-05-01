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

require_once 'include/EditView/EditView2.php';

/**
 * Bug #48570
 * Currency always default to US Dollars when you edit an opportunity
 *
 * @author mgusev@sugarcrm.com
 * @ticket 48570
 */
class Bug48570Test extends TestCase
{
    protected function setUp(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }


    /**
     * Create new currency
     * Create fake Opportunity with created currency
     * Try to get select for currency field and assert that new currency is selected
     *
     * @return void
     * @group 48570
     */
    public function testCurrencySelect()
    {
        $currency = new Currency();
        $currency->iso4217 = 'EUR';
        $currency->name = 'Euro';
        $currency->symbol = 'E';
        $currency->conversion_rate = 1.5;
        $currency->status = 'Active';
        $currency->save();

        $focus = new Opportunity();
        $focus->id = self::class;
        $focus->currency_id = $currency->id;
        $focus->team_id = '1';

        $editView = new EditView();
        $editView->showVCRControl = false;
        $editView->view = 'EditView';
        $editView->setup('Opportunities', $focus, 'modules/Opportunities/metadata/editviewdefs.php');
        $editView->process();

        $currency->mark_deleted($currency->id);

        $this->assertMatchesRegularExpression('/<option value="' . $focus->currency_id . '" selected>/sim', $editView->fieldDefs['currency_id']['value'], 'No selected option here');
    }
}
