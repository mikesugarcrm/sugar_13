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
 * Created by JetBrains PhpStorm.
 * User: idymovsky
 * Date: 2/14/12
 * Time: 1:57 PM
 * To change this template use File | Settings | File Templates.
 */
class Bug50358Test extends TestCase
{
    public $view;

    protected function setUp(): void
    {
        $_REQUEST['module'] = 'Accounts';
        $this->view = new ViewWizard();
    }

    protected function tearDown(): void
    {
        unset($this->view);
    }

    public function currencyDataProvider()
    {
        return [
            [
                [
                    '-99' => [
                        'name' => 'USD',
                        'symbol' => 'USD',
                    ],
                    '1' => [
                        'name' => 'EUR',
                        'symbol' => '&',
                    ],
                    '2' => [
                        'name' => 'AAA',
                        'symbol' => '*',
                    ],
                ],
                "currencies[0] = 'USD';\ncurrencies[1] = '*';\ncurrencies[2] = '&';",
            ],
            [
                [
                    '-99' => [
                        'name' => 'USD',
                        'symbol' => 'USD',
                    ],
                    '1' => [
                        'name' => 'AAA',
                        'symbol' => '*',
                    ],
                    '2' => [
                        'name' => 'EUR',
                        'symbol' => '&',
                    ],
                ],
                "currencies[0] = 'USD';\ncurrencies[1] = '*';\ncurrencies[2] = '&';",
            ],
        ];
    }

    /**
     * @dataProvider currencyDataProvider
     */
    public function testPhpArrayToJavascriptArrayConvertion($currencyArray, $javascriptArrayString)
    {
        $this->assertEquals(trim($javascriptArrayString), trim($this->view->correctCurrenciesSymbolsSort($currencyArray)));
    }
}
