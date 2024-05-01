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
 * This test checks to see if custom elements can be defined in a popupdef and be handled by PopupMetaDataParser.php
 * @ticket 50308
 */
class Bug50308Test extends TestCase
{
    public $customFilePath = 'custom/modules/Users/metadata/popupdefs.php';
    public $customFileDir = 'custom/modules/Users/metadata';
    public $originalPopupMeta = [];
    public $newPopupMeta = ['moduleMain' => ['one', 'two'], 'varName' => ['one', 'two'], 'orderBy' => ['one', 'two'], 'whereClauses' => ['one', 'two'], 'searchInputs' => ['one', 'two'], 'create' => ['one', 'two']];

    protected function setUp(): void
    {
        $popupMeta = null;
        //back up users popup if it exists
        if (is_file($this->customFilePath)) {
            include $this->customFilePath;
            $this->originalPopupMeta = $popupMeta;
            $this->newPopupMeta = $popupMeta;
        } else {
            //lets create the directory if it does not exist
            if (!is_dir($this->customFileDir)) {
                sugar_mkdir($this->customFileDir);
            }
        }

        //define and add the new elements
        $this->newPopupMeta['addToReserve'] = ['whereStatement', 'templateMeta'];
        $this->newPopupMeta['whereStatement'] = 'select money from yourWallet where deposit = "myPocket"';
        $this->newPopupMeta['templateMeta'] = ['one', 'two'];
        $this->newPopupMeta['disappear'] = 'this element was not defined and should be processed';
    }

    protected function tearDown(): void
    {
        //remove custom file
        unlink($this->customFilePath);
        //recreate custom file using old data if it was collected
        if (!empty($this->originalPopupMeta)) {
            $meta = "<?php\n \$popupMeta = array (\n";
            foreach ($this->originalPopupMeta as $k => $v) {
                $meta .= "    '$k' => " . var_export_helper($v) . ",\n";
            }
            $meta .= ");\n";

            file_put_contents($this->customFilePath, $meta);
        }

        unset($this->customFilePath);
        unset($this->customFileDir);
        unset($this->originalPopupMeta);
        unset($this->newPopupMeta);
    }

    /*
     * This method writes out the custom popupdef file to custom users directory, then runs the save function on the  popup metadata parser
     * the tests assert that the custom elements are preserved by the parser
     */
    public function testUsingCustomPopUpElements()
    {
        $popupMeta = null;
        //declare the vars global and then include the modules file to make sure they are available during testing
        global $moduleList, $beanList, $beanFiles;
        include 'include/modules.php';

        if (empty($GLOBALS['app_list_strings'])) {
            $language = $GLOBALS['current_language'];
            $GLOBALS['app_list_strings'] = return_app_list_strings_language($language);
        }
        //write out to file and assert that the file was written, or we shouldn't continue
        $meta = "<?php\n \$popupMeta = array (\n";
        foreach ($this->newPopupMeta as $k => $v) {
            $meta .= "    '$k' => " . var_export_helper($v) . ",\n";
        }
        $meta .= ");\n";

        $writeResult = file_put_contents($this->customFilePath, $meta) !== false;
        $this->assertGreaterThan(0, $writeResult, 'there was an error writing custom popup meta to file using this path: ' . $this->customFilePath);

        //create new instance of popupmetadata parser
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser(MB_POPUPLIST, 'Users');

        //run save to write out the file using the new array elements.
        $parser->handleSave(false);

        //assert the file still exists
        $this->assertTrue(is_file($this->customFilePath), ' PopupMetaDataParser::handleSave() could not write out the file as expected.');

        //include the file again to get the new popup meta array
        include $this->customFilePath;
        $popupKeys = array_keys($popupMeta);
        //assert that one of the new elements is there
        $this->assertContains('whereStatement', $popupKeys, 'an element that was defined in addToReserve was not processed and save within PopupMetaDataParser::handleSave()');

        //assert that the element that was written but not defined in 'addToReserve' is no longer there
        $this->assertNotContains('disappear', $popupKeys, 'an element that was added but NOT defined in addToReserve was incorrectly processed and saved within PopupMetaDataParser::handleSave().');
    }
}
