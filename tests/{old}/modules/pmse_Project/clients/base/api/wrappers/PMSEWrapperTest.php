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

class PMSEWrapperTest extends TestCase
{
    /**
     * Tests building the condition for a delete query
     * @param SugarBean $bean
     * @param array $keys
     * @param string $expect
     * @dataProvider buildConditionFromKeysProvider
     */
    public function testBuildConditionFromKeys($bean, $keys, $expect)
    {
        $mock = new PMSEWrapperMock();
        $actual = $mock->getBuiltCondition($bean, $keys);
        $this->assertEquals($expect, $actual);
    }

    public function buildConditionFromKeysProvider()
    {
        $db_id = null;
        $db_foo = null;
        $db_baz = null;
        $db_mck = null;
        // Lets get the DB instance since we will need that
        $db = DBManagerFactory::getInstance();

        // Get a mock bean
        $bean = new EmptyBean();
        $bean->id = 'test-wrapper';
        $bean->foo = 'bar';
        $bean->baz = 'zim';
        $bean->mck = 'TEST';
        $bean->nope = '';

        // Make db quoted versions of these vars
        foreach (['id', 'foo', 'baz', 'mck', 'nope'] as $var) {
            $dbvar = 'db_' . $var;
            ${$dbvar} = $db->quoted($bean->$var);
        }

        // return what is needed for the provider
        return [
            // Test all keys are on bean
            [
                'bean' => $bean,
                'keys' => ['id', 'foo', 'baz', 'mck',],
                'expect' => " id = $db_id AND foo = $db_foo AND baz = $db_baz AND mck = $db_mck",
            ],
            // Test some keys are on bean
            [
                'bean' => $bean,
                'keys' => ['id', 'baz',],
                'expect' => " id = $db_id AND baz = $db_baz",
            ],
            // Test keys that might not be on bean
            [
                'bean' => $bean,
                'keys' => ['id', 'bol', 'mck',],
                'expect' => " id = $db_id AND mck = $db_mck",
            ],
            // Test one key
            [
                'bean' => $bean,
                'keys' => ['id'],
                'expect' => " id = $db_id",
            ],
            // Test no keys
            [
                'bean' => $bean,
                'keys' => ['bel', 'biv', 'bax',],
                'expect' => '',
            ],
        ];
    }
}

class PMSEWrapperMock extends PMSEWrapper
{
    public function getBuiltCondition(SugarBean $bean, array $keys)
    {
        return parent::buildConditionFromKeys($bean, $keys);
    }
}
