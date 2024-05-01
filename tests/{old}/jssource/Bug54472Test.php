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

/*
* This test will confirm that JSGroupings are concatenated using the Extensions Framework
*
*/


class Bug54472Test extends TestCase
{
    private $beforeArray;
    private $removeJSG_Dir = false;


    protected function setUp(): void
    {
        $js_groupings = null;
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

        //lets retrieve the original jsgroupings file to populate the js_grouping array to compare against later on
        include 'jssource/JSGroupings.php';

        //store the grouping value before any changes
        $this->beforeArray = $js_groupings;

        //create supporting files in seperate function
        $this->createSupportingFiles();

        //run repair so the extension files are created and updated
        $rac = new RepairAndClear();
        $rac->repairAndClearAll(['rebuildExtensions'], [], false, false);
    }


    /*
     * This function creates supporting directory structure and files to carry out the test
     */
    private function createSupportingFiles()
    {
        //create the js group directory in the proper extension location if needed
        if (!file_exists('custom/Extension/application/Ext/JSGroupings/')) {
            mkdir_recursive('custom/Extension/application/Ext/JSGroupings/', true);
            $this->removeJSG_Dir = true;
        }

        //create the first grouping file and define the first group
        if ($fh = @fopen('custom/Extension/application/Ext/JSGroupings/Jgroup0.php', 'w+')) {
            $jsgrpStr = '<?php
$js_groupings [\'testEntrySite\'] = array("include/javascript/calendar.js" => "include/javascript/sugar_test_grp1.js", "include/javascript/cookie.js" => "include/javascript/sugar_test_grp1.js");
';
            fputs($fh, $jsgrpStr);
            fclose($fh);
        }


        //now create a second custom grouping file
        if ($fhAcc = @fopen('custom/Extension/application/Ext/JSGroupings/Jgroup1.php', 'w+')) {
            $jsgrpACCStr = '<?php
$js_groupings [\'testEntryMod\'] = array("include/javascript/calendar.js" => "include/javascript/sugar_testAcc_grp1.js", "include/javascript/quickCompose.js" => "include/javascript/sugar_testAcc_grp1.js");
';
            fputs($fhAcc, $jsgrpACCStr);
            fclose($fhAcc);
        }
    }

    protected function tearDown(): void
    {
        //remove the 2 grouping files and their directories
        if (file_exists('custom/Extension/application/Ext/JSGroupings/Jgroup0.php')) {
            unlink('custom/Extension/application/Ext/JSGroupings/Jgroup0.php');
        }
        if (file_exists('custom/Extension/application/Ext/JSGroupings/Jgroup1.php')) {
            unlink('custom/Extension/application/Ext/JSGroupings/Jgroup1.php');
        }
        if ($this->removeJSG_Dir && file_exists('custom/Extension/application/Ext/JSGroupings')) {
            @rmdir('custom/Extension/application/Ext/JSGroupings');
        }

        //unset before array
        unset($this->beforeArray);

        //run repair so the extension files are reset back to original state
        $trac = new RepairAndClear();
        $trac->repairAndClearAll(['rebuildExtensions'], [], false, false);
        SugarTestHelper::tearDown();
    }

    public function testGetJSGroupingCustomEntries()
    {
        $js_groupings = null;
        //include jsgroupings file again, this time it should pick up the 2 new groups from the extensions.
        include 'jssource/JSGroupings.php';

        //assert that the array count has increased, this confirms it is grabbing the files correctly
        $this->assertGreaterThan(safeCount($this->beforeArray), $js_groupings === null ? 0 : count($js_groupings), 'JSGrouping array was not concatenated correctly, the number of elements should have increased');

        //Check for the individual entries to confirm they are being concatenated and not overwritten
        $this->assertArrayHasKey('testEntrySite', $js_groupings, 'JSGrouping array was not concatenated correctly, site entry is missing');
        $this->assertArrayHasKey('testEntryMod', $js_groupings, 'JSGrouping array was not concatenated correctly, module entry is missing');
    }
}
