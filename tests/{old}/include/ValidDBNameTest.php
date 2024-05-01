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

require_once 'include/utils.php';

class ValidDBNameTest extends TestCase
{
    public function testShortNameUneffected()
    {
        $this->assertEquals(
            'idx_test_123_id',
            getValidDBName('idx_test_123_id')
        );
    }

    public function testmaxLengthParam()
    {
        $this->assertEquals(
            'idx_test_123_456_foo_bar_id',
            getValidDBName('idx_test_123_456_foo_bar_id', false)
        );
    }

    public function testEnsureUnique()
    {
        $this->assertEquals(
            getValidDBName('idx_test_123_456_789_foo_bar_id', true),
            getValidDBName('idx_test_123_456_789_foo_bar_id', true)
        );

        $this->assertNotEquals(
            getValidDBName('idx_test_123_456_789_foo_bar_id', true),
            getValidDBName('idx_test_123_446_789_foo_bar_id', true)
        );
    }

    public function testValidMySQLNameReturnsTrue()
    {
        $this->assertTrue(isValidDBName('sugarCRM', 'mysql'));
        $this->assertTrue(isValidDBName('sugar-crm', 'mysql'));
        $this->assertTrue(isValidDBName('sugar_crm', 'mysql'));
        $this->assertTrue(isValidDBName('sugar-crm', 'mysql'));
        $this->assertTrue(isValidDBName('sugar-CRM_ver6', 'mysql'));
    }

    public function testInvalidMySQLNameReturnsFalse()
    {
        $this->assertFalse(isValidDBName('sugar/crm', 'mysql'));
        $this->assertFalse(isValidDBName('sugar\crm', 'mysql'));
        $this->assertFalse(isValidDBName('sugar.crm', 'mysql'));
    }


    public function testValidOracleNameReturnsTrue()
    {
        $this->assertTrue(isValidDBName('sugarCRM', 'oci8'));
        $this->assertTrue(isValidDBName('sugar_crm', 'oci8'));
        $this->assertTrue(isValidDBName('sugarCRM_ver6', 'oci8'));
    }

    public function testInvalidOracleNameReturnsFalse()
    {
        $this->assertFalse(isValidDBName('sugar\CRM', 'oci8'));
        $this->assertFalse(isValidDBName('sugar crm', 'oci8'));
        $this->assertFalse(isValidDBName('sugarCRM_ver#63', 'oci8'));
    }



    public function testValidMSSQLNameReturnsTrue()
    {
        $this->assertTrue(isValidDBName('sugarCRM', 'mssql'));
        $this->assertTrue(isValidDBName('sugar_crm', 'mssql'));
        $this->assertTrue(isValidDBName('sugarCRM_ver6', 'mssql'));
    }

    public function testInvalidMSSQLNameReturnsFalse()
    {
        $this->assertFalse(isValidDBName('622sugarCRM', 'mssql'));
        $this->assertFalse(isValidDBName('sugar crm', 'mssql'));
        $this->assertFalse(isValidDBName('#sugarCRM_ver6', 'mssql'));
    }

    public function longNameProvider()
    {
        return [
            ['eeeee_eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee_opportunities', 'eeeee_eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee1_opportunities'],
            ['abc_auctions_abc_contactauctions', 'abc_auctionsleadsources_abc_contact_auctions'],
        ];
    }

    public function testDots()
    {
        $this->assertEquals('foo.bar', getValidDbName('foo.bar'));
        $this->assertEquals('bar.baz', getValidDbName('foo.bar.baz'));
        $this->assertEquals('foobar.foobaz', getValidDbName('foo#bar.foo#baz'));
    }

    /**
     * @dataProvider longNameProvider
     */
    public function testLongNameAffected($name_1, $name_2)
    {
        $this->assertNotEquals(getValidDBName($name_1), getValidDBName($name_2));
    }
}
