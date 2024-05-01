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

class ImportFileLimitTest extends TestCase
{
    private $fileSample1;
    private $fileSample2;
    private $fileSample3;
    private $fileSample4;

    private $fileLineCount1 = 555;
    private $fileLineCount2 = 111;
    private $fileLineCount3 = 2;
    private $fileLineCount4 = 0;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->fileSample1 = SugarTestImportUtilities::createFile($this->fileLineCount1, 3);
        $this->fileSample2 = SugarTestImportUtilities::createFile($this->fileLineCount2, 3);
        $this->fileSample3 = SugarTestImportUtilities::createFile($this->fileLineCount3, 3);
        $this->fileSample4 = SugarTestImportUtilities::createFile($this->fileLineCount4, 3);
    }

    protected function tearDown(): void
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testGetFileRowCount()
    {
        $if1 = new ImportFile($this->fileSample1, ',', '"', false);
        $if2 = new ImportFile($this->fileSample2, ',', '"', false);
        $if3 = new ImportFile($this->fileSample3, ',', '"', false);
        $if4 = new ImportFile($this->fileSample4, ',', '"', false);

        $this->assertEquals($this->fileLineCount1, $if1->getNumberOfLinesInfile());
        $this->assertEquals($this->fileLineCount2, $if2->getNumberOfLinesInfile());
        $this->assertEquals($this->fileLineCount3, $if3->getNumberOfLinesInfile());
        $this->assertEquals($this->fileLineCount4, $if4->getNumberOfLinesInfile());
    }
}
