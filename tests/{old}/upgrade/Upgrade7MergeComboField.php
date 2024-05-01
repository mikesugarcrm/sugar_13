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

require_once 'upgrade/scripts/post/7_Merge7Templates.php';

class testComboFieldMerge extends UpgradeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SugarTestHelper::saveFile('custom/modules/Notes/clients/base/views/record/record.php');
        mkdir_recursive('custom/modules/Notes/clients/base/views/record');
        copy('tests/{old}/upgrade/7_Merge7Templates/notes_example.php', 'custom/modules/Notes/clients/base/views/record/record.php');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test7MergeComboFields()
    {
        $viewdefs = [];
        //Load up upgrader params necessary for script to run
        include 'tests/{old}/upgrade/7_Merge7Templates/notes_example2.php';
        $this->upgrader->state['for_merge']['modules/Notes/clients/base/views/record/record.php']
            = $viewdefs;

        //Run script
        $script = $this->upgrader->getScript('post', '7_Merge7Templates');
        $script->from_version = '7.2.1';
        $script->run();

        //Get new viewdefs present in the custom directory
        include 'custom/modules/Notes/clients/base/views/record/record.php';
        $record = $viewdefs['Notes']['base']['view']['record'];

        //Ensure combo fields that shouldn't have been added were not added
        $search = ['date_entered_by', 'date_modified_by'];
        $this->assertFalse($this->searchRecord($record, $search));
    }

    /*
     * Return true if any of the fields in $record have a name that exists in $needleList
     * @param array $record - Haystack
     * @param array $needle_list - Needles
     */
    public function searchRecord($record, $needle_list)
    {
        foreach ($record['panels'] as $panel) {
            foreach ($panel['fields'] as $field) {
                if (is_array($field) && !empty($field['name']) && in_array($field['name'], $needle_list)) {
                    return true;
                } elseif (is_string($field) && in_array($field, $needle_list)) {
                    return true;
                }
            }
        }

        return false;
    }
}
