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
 * Bug #56584
 * @ticket 56584
 */
class Bug56584Test extends TestCase
{
    /**
     * @var string
     */
    private $testFile;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', ['Import']);
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', [true, 1]);

        $this->testFile = 'tests/{old}/modules/Tasks/Bug56584Test.csv';

        $_REQUEST = [
            'colnum_0' => 'contact_name',
            'colnum_1' => 'name',
            'colnum_2' => 'status',
            'columncount' => '3',
            'importlocale_charset' => 'UTF-8',
            'importlocale_currency' => '-99',
            'importlocale_dateformat' => 'd/m/Y',
            'importlocale_dec_sep' => '.',
            'importlocale_default_currency_significant_digits' => '2',
            'importlocale_default_locale_name_format' => 's f l',
            'importlocale_num_grp_sep' => ',',
            'importlocale_timeformat' => 'H:i',
            'importlocale_timezone' => 'Europe/Helsinki',
            'import_module' => 'Tasks',
        ];
    }

    protected function tearDown(): void
    {
        $uid = $GLOBALS['current_user']->id;
        $GLOBALS['db']->query('DELETE FROM contacts ' .
            "WHERE created_by = '$uid' ");
        $GLOBALS['db']->query('DELETE FROM tasks ' .
            "WHERE created_by = '$uid' ");

        SugarTestHelper::tearDown();
    }

    public function testImport()
    {
        // FIXME TY-1324: figure out why this test is failing
        global $db, $current_user;

        $taskBean = new Task();
        $importSource = new ImportFile($this->testFile, ',', '', false);
        $importer = new Importer($importSource, $taskBean);
        $contactBean = new Contact();
        $contacts = [];

        $importer->import();

        $result = $db->query('SELECT id, first_name, last_name ' .
            "FROM $contactBean->table_name " .
            "WHERE created_by='$current_user->id'");

        while ($row = $db->fetchRow($result)) {
            $contacts[] = $row;
        }

        $this->assertEquals(1, count($contacts), 'Invalid number of contacts created.');

        foreach ($contacts as $record) {
            $taskBean->retrieve_by_string_fields([
                'contact_id' => $record['id'],
            ]);

            $this->assertNotEmpty($record['first_name'], 'First name of contact "' . $record['id'] . '" is empty.');
            $this->assertNotEmpty($record['last_name'], 'Last name of contact "' . $record['id'] . '" is empty.');
            $this->assertEquals($record['first_name'] . ' ' . $record['last_name'], $taskBean->contact_name);
        }
    }

    public function contactParams()
    {
        return [
            ['John Doe', 'John', 'Doe'],
            ['John Doe Jr.', 'John', 'Doe Jr.'],
            ['Doe', '', 'Doe'],
        ];
    }

    /**
     * @dataProvider contactParams
     * @param string $rawValue
     * @param string $firstName
     * @param string $lastName
     */
    public function testAssignConcatenatedName($rawValue, $firstName, $lastName)
    {
        $testBean = new Contact();
        $fieldDef = $testBean->getFieldDefinition('name');

        assignConcatenatedValue($testBean, $fieldDef, $rawValue);

        $this->assertEquals($firstName, $testBean->first_name, 'First name is invalid.');
        $this->assertEquals($lastName, $testBean->last_name, 'Last name is invalid.');
    }

    public function teamParams()
    {
        return [
            ['Big Team', 'Big', 'Team'],
            ['Very Big Team', 'Very', 'Big Team'],
            ['Team', 'Team', ''],
        ];
    }

    /**
     * @dataProvider teamParams
     * @param string $rawValue
     * @param string $name
     * @param string $name2
     */
    public function testAssignConcatenatedTeamName($rawValue, $name, $name2)
    {
        global $dictionary;

        $testBean = new Team();
        $fieldDef = $dictionary['User']['fields']['team_name'];

        assignConcatenatedValue($testBean, $fieldDef, $rawValue);

        $this->assertEquals($name, $testBean->name, 'First name is invalid.');
        $this->assertEquals($name2, $testBean->name_2, 'Last name is invalid.');
    }
}
