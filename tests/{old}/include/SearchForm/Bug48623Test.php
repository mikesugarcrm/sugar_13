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

require_once 'include/SearchForm/SearchForm2.php';

class Bug48623Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['Opportunities']);
        $GLOBALS['current_user']->setPreference('timezone', 'EDT');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider dateTestProvider
     */
    public function testParseDateExpressionWithAndWithoutTimezoneAdjustment($expected1, $expected2, $operator, $type)
    {
        global $timedate;

        $seed = new Opportunity();
        $sf = new SearchForm2Wrap($seed, 'Opportunities', 'index');

        $where = $sf->publicParseDateExpression($operator, 'opportunities.date_closed', $type);
        $this->assertMatchesRegularExpression($expected1, $where);
        $this->assertMatchesRegularExpression($expected2, $where);
    }

    public function dateTestProvider()
    {
        $noTzRegExp1 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 00:00:00\'/';
        $noTzRegExp2 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 23:59:59\'/';
        $tzRegExp1 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 0[4,5]:00:00\'/';
        $tzRegExp2 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 0[3,4]:59:59\'/';
        return [
            //  $expected1, expected2, $operator, $type
            [$noTzRegExp1, $noTzRegExp2, 'this_month', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'last_month', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'next_month', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'this_year', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'last_year', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'next_year', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'yesterday', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'today', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'tomorrow', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'last_7_days', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'next_7_days', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'last_30_days', 'date'],
            [$noTzRegExp1, $noTzRegExp2, 'next_30_days', 'date'],

            [$tzRegExp1, $tzRegExp2, 'this_month', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'last_month', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'next_month', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'this_year', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'last_year', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'next_year', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'yesterday', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'today', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'tomorrow', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'last_7_days', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'next_7_days', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'last_30_days', 'datetime'],
            [$tzRegExp1, $tzRegExp2, 'next_30_days', 'datetime'],
        ];
    }
}


/**
 * Wrap the SearchForm class to make a protected function public
 */
class SearchForm2Wrap extends SearchForm
{
    public function publicParseDateExpression($operator, $db_field, $field_type)
    {
        return $this->parseDateExpression($operator, $db_field, $field_type);
    }
}
