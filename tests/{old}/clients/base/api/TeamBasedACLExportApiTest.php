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

class TeamBasedACLExportApiTest extends TestCase
{
    /**
     * @var ExportApi
     */
    protected $api;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var RecordListApi
     */
    protected $recordList;

    /**
     * @var string
     */
    protected $recordListId;

    /**
     * @var TeamSet
     */
    protected $teamSetUserIn;

    /**
     * @var Team
     */
    protected $teamUserIn;

    /**
     * @var TeamSet
     */
    protected $teamSetUserNot;

    /**
     * @var array
     */
    protected $records = [];

    /**
     * @var SugarBean
     */
    protected $beanTBA;

    /**
     * @var SugarBean
     */
    protected $beanNotTBA;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, false]);
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->api = new ExportApi();
        $this->recordList = new RecordListApi();
        $tbaConfigurator = new TeamBasedACLConfigurator();

        $this->teamUserIn = SugarTestTeamUtilities::createAnonymousTeam();
        $this->teamUserIn->add_user_to_team($GLOBALS['current_user']->id);

        $this->teamSetUserIn = BeanFactory::newBean('TeamSets');
        $this->teamSetUserIn->addTeams([$this->teamUserIn->id]);

        $teamUserNot = SugarTestTeamUtilities::createAnonymousTeam();

        $this->teamSetUserNot = BeanFactory::newBean('TeamSets');
        $this->teamSetUserNot->addTeams([$teamUserNot->id]);

        $this->beanTBA = SugarTestAccountUtilities::createAccount(null, [
            'assigned_user_id' => null,
            'acl_team_set_id' => $this->teamSetUserIn->id,
        ]);

        $this->records[] = $this->beanTBA->id;

        $this->beanNotTBA = SugarTestAccountUtilities::createAccount(null, [
            'assigned_user_id' => null,
            'acl_team_set_id' => $this->teamSetUserNot->id,
        ]);

        $this->records[] = $this->beanNotTBA->id;

        $listData = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => $this->module, 'records' => $this->records]
        );
        $this->recordListId = $listData['id'];

        $tbaConfigurator->setGlobal(true);
        $tbaConfigurator->setForModule($this->module, true);

        $aclData = [
            'module' => [
                'access' => [
                    'aclaccess' => ACL_ALLOW_ALL,
                ],
                'export' => [
                    'aclaccess' => ACL_ALLOW_SELECTED_TEAMS,
                ],
            ],
        ];

        ACLAction::setACLData($GLOBALS['current_user']->id, $this->module, $aclData);
    }

    protected function tearDown(): void
    {
        $this->recordList->recordListDelete(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'record_list_id' => $this->recordListId]
        );
        $this->teamSetUserIn->mark_deleted($this->teamSetUserIn->id);
        $this->teamSetUserNot->mark_deleted($this->teamSetUserNot->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Should export only records whose selected teams in user's teams.
     */
    public function testExportTBA()
    {
        $result = $this->api->export(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'record_list_id' => $this->recordListId]
        );

        $this->assertStringContainsString($this->beanTBA->id, $result);
        $this->assertStringNotContainsString($this->beanNotTBA->id, $result);

        $this->assertStringContainsString($this->teamSetUserIn->id, $result);
        $this->assertStringContainsString($this->teamUserIn->name, $result);
    }

    /**
     * Test that empty team selected id is not filled with Global during export.
     * Even if TBA is off the selected team set should be exported.
     */
    public function testExportEmptyTeamSetSelected()
    {
        $tbaConfigurator = new TeamBasedACLConfigurator();
        $tbaConfigurator->setForModule($this->module, false);

        $this->beanTBA->acl_team_set_id = '';
        $this->beanTBA->save();

        $listData = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => $this->module, 'records' => [$this->beanTBA->id]]
        );

        $csvString = $this->api->export(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'record_list_id' => $listData['id']]
        );
        $actualGlobalTeam = substr_count($csvString, 'Global');

        $this->assertEquals(1, $actualGlobalTeam);
    }
}
