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

class GetSearchWhereValueTest extends TestCase
{
    public $intField;

    protected function setUp(): void
    {
        $this->intField = SugarFieldHandler::getSugarField('int');
    }

    protected function tearDown(): void
    {
        unset($this->intField);
    }

    /**
     * testGetSearchWhereValue
     *
     * tests SugarFieldInt::getSearchWhereValue() function
     *
     * @dataProvider  getSearchWhereProvider
     */
    public function testGetSearchWhereValue($exp, $val)
    {
        $this->assertSame($exp, $this->intField->getSearchWhereValue($val));
    }

    /**
     * getSearchWhereProvider
     *
     * provides values for testing SugarFieldInt::getSearchWhereValue
     *
     * @return Array values for testing
     */
    public function getSearchWhereProvider()
    {
        return [
            [123, 123],
            [-1, 'test'],
            ['12,14,16', '12,14,16'],
            ['12,-1,16', '12,junk,16'],
            ['-1,12,-1,16,34,124,-1', 'stuff,12,junk,16,34,124,morejunk'],
            [-1, ''],
        ];
    }
}
