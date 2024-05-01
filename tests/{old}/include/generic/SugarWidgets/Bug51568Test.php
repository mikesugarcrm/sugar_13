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
 * Bug #51568
 *  Currency symbol didn't export to the CVS or pdf file in report module
 *
 * @author aryamrchik@sugarcrm.com
 * @ticket 51568
 */
class Bug51568Test extends TestCase
{
    /**
     * @var LayoutManager
     */
    protected $lm;

    /**
     * @var Currency
     */
    protected $currency_51568;

    /**
     * @var Currency
     */
    protected $currency_system;

    /**
     * @var string
     */
    protected $backupSymbol;

    protected function setUp(): void
    {
        global $current_user, $sugar_config;
        SugarTestHelper::setUp('current_user', [true]);
        $current_user->setPreference('dec_sep', ',');
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('default_currency_significant_digits', 3);

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->lm = new LayoutManager();
        $this->lm->setAttribute('reporter', new stdClass());

        $this->currency_51568 = BeanFactory::getBean('Currencies', null, ['use_cache' => false]);
        $this->currency_51568->symbol = 'TT';
        $this->currency_51568->conversion_rate = 0.5;
        $this->currency_51568->save(false);

        $currency = BeanFactory::newBean('Currencies');
        $currency->retrieve(-99);
        $this->backupSymbol = $currency->symbol;
        $currency->symbol = '¥';
        $currency->save(false);
        $sugar_config['default_currency_symbol'] = '¥';
        get_number_seperators(true);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithLayoutDef()
    {
        $data = [
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol,
        ];
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithCurrencyField()
    {
        $data = [
            'fields' => [
                '51568table_some_field_currency' => $this->currency_51568->id,
            ],
        ];
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithAnotherCurrencyField()
    {
        $data = [
            'fields' => [
                '51568TABLE_SOME_FIELD_CURRENCY' => $this->currency_51568->id,
            ],
        ];
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithSystemCurrencyField()
    {
        format_number(0, 0, 0, [
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol,
        ]);

        format_number(0, 0, 0, [
            'currency_id' => -99,
            'currency_symbol' => $this->currency_51568->getDefaultCurrencySymbol(),
        ]);

        $data = [
            'name' => 'some_field_usdoll',
            'column_key' => 'self::some_field_usdoll',
            'fields' => [
                '51568TABLE_SOME_FIELD_USDOLL' => 100500,
            ],
        ];
        $result = $this->getResults($data);
        $this->assertEquals('¥100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithWrongCurrency()
    {
        $data = [
            'currency_id' => '-51568',
            'currency_symbol' => '£',
        ];
        $result = $this->getResults($data);
        $this->assertEquals('¥100.500,000', $result);
    }

    protected function getResults($layout_def_addon)
    {
        $layout_def = [
            'column_key' => 'self::some_field',
            'fields' => [
                '51568TABLE_SOME_FIELD' => 100500,
            ],
            'name' => 'some_field',
            'table_key' => 'self',
            'table_alias' => '51568table',
            'type' => 'currency',
        ];
        foreach ($layout_def_addon as $k => $v) {
            if (is_array($v)) {
                $layout_def = array_merge_recursive($layout_def, [$k => $v]);
            } else {
                $layout_def[$k] = $v;
            }
        }
        $sf = $this->getMockBuilder('SugarWidgetFieldCurrency')
            ->setMethods(['getTruncatedColumnAlias'])
            ->setConstructorArgs([&$this->lm])
            ->getMock();
        $sf->expects($this->any())
            ->method('getTruncatedColumnAlias')
            ->will($this->returnArgument(0));
        return $sf->displayListPlain($layout_def);
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        $currency = BeanFactory::newBean('Currencies');
        $currency->retrieve(-99);
        $currency->symbol = $this->backupSymbol;
        $currency->save(false);
        $sugar_config['default_currency_symbol'] = $this->backupSymbol;

        format_number(0, 0, 0, [
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol,
        ]);

        format_number(0, 0, 0, [
            'currency_id' => -99,
            'currency_symbol' => $this->currency_51568->getDefaultCurrencySymbol(),
        ]);

        $this->currency_51568->mark_deleted($this->currency_51568->id);
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }
}
