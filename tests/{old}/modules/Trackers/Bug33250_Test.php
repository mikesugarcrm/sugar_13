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
 * This test will check the TrackerUtility functionality and how it attempts to create
 * a generic SQL statement that gets tracked and logged when slow queries are tracked.
 */
class Bug33250_Test extends TestCase
{
    public function test_generic_sql_with_matched_quotes()
    {
        $sql = 'SELECT id FROM contacts WHERE first_name = \'Collin\' and last_name = \'Lee\'';
        $generic_sql = TrackerUtility::getGenericSQL($sql);
        $this->assertEquals($generic_sql, "SELECT id FROM contacts WHERE first_name = '?' and last_name = '?'", 'Assert that matched quoted query is properly formatted');
    }

    public function test_generic_sql_with_unmatched_quotes()
    {
        $sql = 'SELECT id FROM contacts WHERE first_name = \'Collin\' and last_name = \'Lee';
        $generic_sql = TrackerUtility::getGenericSQL($sql);
        $this->assertEquals($generic_sql, $sql, 'Assert that unmatched quoted query is the same as input query.');
    }

    public function test_generic_sql_with_escaped_quotes()
    {
        $sql = 'SELECT id FROM contacts WHERE first_name = \'Bill\' and last_name = \'O\\\'Reilly\'';
        $generic_sql = TrackerUtility::getGenericSQL($sql);
        $this->assertEquals($generic_sql, "SELECT id FROM contacts WHERE first_name = '?' and last_name = '?'", 'Assert that matched quoted query is properly formatted');

        $sql = 'INSERT into contacts (first_name, last_name) values (\'Bill\', \'\\\'\\\'\')';
        $generic_sql = TrackerUtility::getGenericSQL($sql);
        $this->assertEquals($generic_sql, "INSERT into contacts (first_name, last_name) values ('?', '?')", 'Assert that matched quoted query is properly formatted');

        //Check for double quoted format as well
        $sql = "SELECT id FROM contacts WHERE first_name = 'Bill' and last_name = 'O\'Reilly'";
        $generic_sql = TrackerUtility::getGenericSQL($sql);
        $this->assertEquals($generic_sql, "SELECT id FROM contacts WHERE first_name = '?' and last_name = '?'", 'Assert that matched quoted query is properly formatted');
    }
}
