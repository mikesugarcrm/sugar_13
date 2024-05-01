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

require_once 'include/export_utils.php';

class TeamBasedACLImportTest extends TestCase
{
    /**
     * @var TeamSet
     */
    protected $teamSet;

    /**
     * @var Importer
     */
    protected $importer;

    /**
     * @var SugarBean
     */
    protected $beanToExport;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '"';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, false]);

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->teamSet = BeanFactory::newBean('TeamSets');
        $this->teamSet->addTeams([$team->id]);

        $this->beanToExport = SugarTestAccountUtilities::createAccount();
        $this->beanToExport->acl_team_set_id = $this->teamSet->id;
        $this->beanToExport->acl_team_names = TeamSetManager::getCommaDelimitedTeams(
            $this->beanToExport->acl_team_set_id,
            $this->beanToExport->team_id
        );
        $this->beanToExport->save();

        $this->importer = $this->getMockBuilder('Importer')
            ->setMethods(['getImportColumns'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $this->teamSet->mark_deleted($this->teamSet->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
    }

    /**
     * The acl_team_set_id field should be importable.
     */
    public function testImporttTBA()
    {
        $importedRecordId = $this->prepareImporter([
            'team_id' => $this->beanToExport->team_id,
            'acl_team_set_id' => $this->beanToExport->acl_team_set_id,
            'acl_team_names' => $this->beanToExport->acl_team_names,
        ]);
        $this->importer->import();
        $importedBean = BeanFactory::getBean($this->module, $importedRecordId);

        $this->assertEquals($this->beanToExport->acl_team_set_id, $importedBean->acl_team_set_id);
    }

    /**
     * The acl_team_set_id field should be populated by acl_team_names.
     */
    public function testImportByTeamSelectedName()
    {
        $expectedTeamSet = $this->beanToExport->acl_team_set_id;

        $this->beanToExport->acl_team_set_id = null;
        $this->beanToExport->save();
        $importedRecordId = $this->prepareImporter([
            'team_id' => $this->beanToExport->team_id,
            // The "acl_team_set_id" is not specified.
            'acl_team_names' => $this->beanToExport->acl_team_names,
        ]);
        $this->importer->import();
        $importedBean = BeanFactory::getBean($this->module, $importedRecordId);

        $this->assertNotEmpty($importedBean->acl_team_set_id);
        $this->assertEquals($expectedTeamSet, $importedBean->acl_team_set_id);
    }

    /**
     * Empty selected set should not affect team_set_id.
     */
    public function testEmptySelectedSet()
    {
        // For some reason team set handler always set a default value in tpl.
        // As a result the super global REQUEST have the construction below.
        // Maybe for setting a real default value which depends on teams in SugarFieldTeamset::importSanitaze().
        $_REQUEST['default_value_team_name'] = 'default_value_team_name';
        $_REQUEST['default_value_acl_team_names'] = 'default_value_acl_team_names';

        $importedRecordId = $this->prepareImporter([
            // Matched to "team_set_id".
            'team_name' => TeamSetManager::getCommaDelimitedTeams($this->teamSet->id),
            // Matched to "acl_team_set_id".
            'acl_team_names' => '',
        ]);
        $this->importer->import();
        $importedBean = BeanFactory::getBean($this->module, $importedRecordId);

        unset($_REQUEST['default_value_team_name']);
        unset($_REQUEST['default_value_acl_team_names']);

        $this->assertEquals($this->teamSet->id, $importedBean->team_set_id);
        $this->assertEquals(null, $importedBean->acl_team_set_id);
    }

    /**
     * Setup importer with source.
     * @param array $nameValue
     * @return string Id of future record.
     */
    protected function prepareImporter(array $nameValue)
    {
        $id = create_guid();
        $exportStr = '';
        $importColumns = [];

        $importColumns[] = 'id';
        $exportStr .= $this->enclosure . $id . $this->enclosure . $this->delimiter;
        foreach ($nameValue as $key => $val) {
            $importColumns[] = $key;
            $exportStr .= $this->enclosure . $val . $this->enclosure . $this->delimiter;
        }
        SugarTestAccountUtilities::setCreatedAccount([$id]);

        $file = SugarTestImportUtilities::createFile();
        file_put_contents($file, $exportStr);

        $source = new ImportFile($file, $this->delimiter, $this->enclosure);

        $this->importer->expects($this->any())->method('getImportColumns')->will(
            $this->returnValue($importColumns)
        );
        $this->importer->__construct($source, $this->beanToExport);

        return $id;
    }
}
