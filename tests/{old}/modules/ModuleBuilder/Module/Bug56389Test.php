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
 * Make a test StudioModule class so as not to destroy ALL of the metadata. This
 * is an ok test since the report is that portal viewdefs aren't updated but all
 * other viewdefs are.
 */
class Bug56389SugarModule extends StudioModule
{
    /**
     * Override the default remove method to include only portal viewdefs field
     * deletes
     *
     * @param $fieldName
     */
    public function removeFieldFromLayouts($fieldName)
    {
        $sources = $this->getPortalLayoutSources();
        foreach ($sources as $defs) {
            //If this module type doesn't support a given metadata type, we will get an exception from getParser()
            try {
                $parser = ParserFactory::getParser($defs['type'], $this->module);
                if ($parser && method_exists($parser, 'removeField') && $parser->removeField($fieldName)) {
                    // don't populate from $_REQUEST, just save as is...
                    $parser->handleSave(false);
                }
            } catch (Exception $e) {
            }
        }
    }
}

/**
 * Bug 56389 - Deleted module fields do not cascade to portal view defs
 */
class Bug56389Test extends TestCase
{
    protected $moduleToTest = 'Cases';
    protected $filesBackedUp = [];
    protected $filesToTest = [
        'modules/Cases/clients/portal/views/list/list.php',
        'modules/Cases/clients/portal/views/record/record.php',
    ];
    protected $filesToTearDown = [];
    protected $fieldToTest = 'name';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('files');

        // Run through our test files and make sure customs, working and core
        // metadata files are backed up. Core files will restore regardless, but
        // others will only restore if there was an original file.
        foreach ($this->filesToTest as $file) {
            // Set aside custom and working files.
            $custom = "custom/$file";
            $working = "custom/working/$file";
            $filesets = [$custom, $working];
            SugarTestHelper::saveFile($filesets);
            SugarTestHelper::saveFile($file);
            foreach ($filesets as $filepath) {
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        }
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Checks whether a fieldname exists in a viewdef
     *
     * @param string $fieldname The fieldname
     * @param array $defs The defs, as of view type
     * @return bool
     */
    private function fieldExistsInDefs($fieldname, $defs)
    {
        foreach ($defs['panels'] as $panel) {
            foreach ($panel['fields'] as $field) {
                if ((is_array($field) && isset($field['name']) && $field['name'] == $fieldname) || $field == $fieldname) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @group Bug56389
     */
    public function testDeleteFieldRemovesPortalViewDefs()
    {
        // First test, get each def and check the test fields
        foreach ($this->filesToTest as $testfile) {
            require $testfile;
            $type = key($viewdefs[$this->moduleToTest]['portal']['view']);
            $exists = $this->fieldExistsInDefs($this->fieldToTest, $viewdefs[$this->moduleToTest]['portal']['view'][$type]);
            $this->assertTrue($exists, "$this->fieldToTest does not exists in $type layout field for $this->moduleToTest");
            unset($viewdefs);
        }

        // Now handle the delete of the fields
        $sm = new Bug56389SugarModule($this->moduleToTest);
        $sm->removeFieldFromLayouts($this->fieldToTest);

        // Now test again, inside of custom files though since that is where
        // changes are saved
        foreach ($this->filesToTest as $testfile) {
            require "custom/$testfile";
            $type = key($viewdefs[$this->moduleToTest]['portal']['view']);
            $exists = $this->fieldExistsInDefs($this->fieldToTest, $viewdefs[$this->moduleToTest]['portal']['view'][$type]);
            $this->assertFalse($exists, "$this->fieldToTest DOES exists in $type layout field for $this->moduleToTest");
            unset($viewdefs);
        }
    }
}
