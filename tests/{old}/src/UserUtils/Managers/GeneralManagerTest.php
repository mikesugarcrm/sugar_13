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

namespace Sugarcrm\SugarcrmTests\UserUtils\Managers;

use BeanFactory;
use PHPUnit\Framework\TestCase;
use SugarBean;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerBasePayload;
use Sugarcrm\Sugarcrm\UserUtils\Managers\GeneralManager;
use SugarTestHelper;
use SugarTestReportUtilities;
use SugarTestSugarFavoriteUtilities;
use SugarTestUserUtilities;

class GeneralManagerTest extends TestCase
{
    /**
     * @var \User|mixed
     */
    public $anonymousUser1;
    /**
     * @var \User|mixed
     */
    public $anonymousUser2;
    /**
     * @var mixed[]
     */
    public $originalDefaultTeams;
    /**
     * @var mixed[]
     */
    public $originalNavigationBarPrefs;
    /**
     * @var mixed[]
     */
    public $originalNotifyOnAssignement;
    /**
     * @var mixed[]
     */
    public $originalReminderOptions;
    /**
     * @var mixed[]
     */
    public $originalEmailType;

    /**
     * setUpBeforeClass function
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        global $current_user;
        /**
         * @var User
         */
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();
    }

    protected function setUp(): void
    {
        global $current_user;
        SugarTestReportUtilities::removeReport('reportId');
        SugarTestReportUtilities::createReport('reportId', ['name' => 'testReport']);
        $report = BeanFactory::retrieveBean('Reports', 'reportId');
        SugarTestSugarFavoriteUtilities::favoriteBean($report, $current_user);
        $this->anonymousUser1 = SugarTestUserUtilities::createAnonymousUser();
        $this->anonymousUser2 = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestReportUtilities::removeAllCreatedReports();
        SugarTestReportUtilities::removeReportsByName('testReport');
        SugarTestSugarFavoriteUtilities::removeAllCreatedFavorites();
        SugarTestSugarFavoriteUtilities::removeFavoritesByRecord('Reports', 'reportId');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Data provider for the clone favorite reports test
     *
     * @return array
     */
    public function providerCloneFavoriteReports(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneFavoriteReports',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Accounts'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * @covers       cloneFavoriteReports
     *
     * @param array $args
     *
     * @dataProvider providerCloneFavoriteReports
     */
    public function testCloneFavoriteReports(array $args): void
    {
        $payload = new InvokerBasePayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new GeneralManager($payload);
        $manager->cloneFavoriteReports();

        $users = $payload->getDestinationUsers();
        foreach ($users as $user) {
            $reports = $manager->getUserFavoriteReports($user);
            $reportIds = array_map(function ($item) {
                return trim($item['record_id']);
            }, $reports);
            $this->assertCount(1, $reports);
            $this->assertContains('reportId', $reportIds);
        }
    }

    /**
     * Data provider for the clone default teams test
     *
     * @return array
     */
    public function providerCloneDefaultTeams(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneDefaultTeams',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Accounts'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * @covers       cloneDefaultTeams
     *
     * @param array $args
     *
     * @dataProvider providerCloneDefaultTeams
     */
    public function testCloneDefaultTeams(array $args): void
    {
        $payload = new InvokerBasePayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new GeneralManager($payload);
        $manager->cloneDefaultTeams();

        $users = $payload->getDestinationUsers();

        $this->originalDefaultTeams = [];

        foreach ($users as $userId) {
            $user = BeanFactory::retrieveBean('Users', $userId);

            $this->originalDefaultTeams[$userId] = [
                'team_id' => $user->team_id,
                'team_set_id' => $user->team_set_id,
            ];

            $this->assertEquals('1', $user->team_id);
            $this->assertEquals('1', $user->team_set_id);
        }
    }

    /**
     * Data provider for cloning navigation bar
     *
     * @return array
     */
    public function providerCloneNavigationBarModuleSelection(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneNavigationBar',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Accounts'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * test for cloning navigation bar
     *
     * @param array $args
     * @return void
     * @dataProvider providerCloneNavigationBarModuleSelection
     */
    public function testCloneNavigationBarModuleSelection(array $args): void
    {
        $payload = new InvokerBasePayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new GeneralManager($payload);
        $manager->cloneNavigationBarModuleSelection();

        $sourceUserId = $payload->getSourceUser();
        $sourceUser = \BeanFactory::retrieveBean('Users', $sourceUserId);
        $displayPref = $sourceUser->getPreference('display_tabs');
        $hidePref = $sourceUser->getPreference('hide_tabs');

        $this->originalNavigationBarPrefs = [];

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $user = \BeanFactory::retrieveBean('Users', $userId);
            $targetedDisplayPref = $user->getPreference('display_tabs');
            $targetedHidePref = $user->getPreference('hide_tabs');

            $this->originalNavigationBarPrefs[$userId] = [
                'display_tabs' => $targetedDisplayPref,
                'hide_tabs' => $targetedHidePref,
            ];

            $this->assertEquals($displayPref, $targetedDisplayPref);
            $this->assertEquals($hidePref, $targetedHidePref);
        }
    }

    /**
     * provider for notify on assignement
     *
     * @return array
     */
    public function providerCloneNotifyOnAssignment(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneNotifyOnAssignment',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Accounts'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * test for notify on assignment
     *
     * @param array $args
     * @return void
     * @dataProvider providerCloneNotifyOnAssignment
     */
    public function testCloneNotifyOnAssignment(array $args): void
    {
        $payload = new InvokerBasePayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new GeneralManager($payload);
        $manager->cloneNotifyOnAssignment();

        $sourceUserId = $payload->getSourceUser();
        $sourceUser = \BeanFactory::retrieveBean('Users', $sourceUserId);
        $notifyOnAssignment = $sourceUser->receive_notifications;

        $this->originalNotifyOnAssignement = [];

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $user = \BeanFactory::retrieveBean('Users', $userId);
            $targetedNotifyOnAssignment = $user->receive_notifications;
            $this->originalNotifyOnAssignement[$userId] = $targetedNotifyOnAssignment;

            $this->assertEquals($notifyOnAssignment, $targetedNotifyOnAssignment);
        }
    }

    /**
     * provider for cloning reminder options
     *
     * @return array
     */
    public function providerCloneReminderOptions(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneReminderOptions',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Accounts'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * test for cloning reminder options
     *
     * @param array $args
     * @return void
     * @dataProvider providerCloneReminderOptions
     */
    public function testCloneReminderOptions(array $args): void
    {
        $payload = new InvokerBasePayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new GeneralManager($payload);
        $manager->cloneReminderOptions();

        $sourceUserId = $payload->getSourceUser();
        $sourceUser = \BeanFactory::retrieveBean('Users', $sourceUserId);
        $reminderChecked = $sourceUser->getPreference('reminder_checked');
        $reminderTime = $sourceUser->getPreference('reminder_time');
        $emailReminderChecked = $sourceUser->getPreference('email_reminder_checked');
        $emailReminderTime = $sourceUser->getPreference('email_reminder_time');

        $this->originalReminderOptions = [];

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $user = \BeanFactory::retrieveBean('Users', $userId);
            $targetedReminderChecked = $user->getPreference('reminder_checked');
            $targetedReminderTime = $user->getPreference('reminder_time');
            $targetedEmailReminderChecked = $user->getPreference('email_reminder_checked');
            $targetedEmailReminderTime = $user->getPreference('email_reminder_time');

            $this->originalReminderOptions[$userId] = [
                'reminder_checked' => $targetedReminderChecked,
                'reminder_time' => $targetedReminderTime,
                'email_reminder_checked' => $targetedEmailReminderChecked,
                'email_reminder_time' => $targetedEmailReminderTime,
            ];

            $this->assertEquals($reminderChecked, $targetedReminderChecked);
            $this->assertEquals($reminderTime, $targetedReminderTime);
            $this->assertEquals($emailReminderChecked, $targetedEmailReminderChecked);
            $this->assertEquals($emailReminderTime, $targetedEmailReminderTime);
        }
    }

    /**
     * Provider for cloning email client
     *
     * @return array
     */
    public function providerCloneSugarEmailClient(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneSugarEmailClient',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Accounts'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * test for cloning sugar email client
     *
     * @param array $args
     * @return void
     *
     * @dataProvider providerCloneSugarEmailClient
     */
    public function testCloneSugarEmailClient(array $args): void
    {
        $payload = new InvokerBasePayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new GeneralManager($payload);
        $manager->cloneSugarEmailClient();

        $sourceUserId = $payload->getSourceUser();
        $sourceUser = \BeanFactory::retrieveBean('Users', $sourceUserId);
        $emailType = $sourceUser->getPreference('email_link_type');

        $this->originalEmailType = [];

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $user = \BeanFactory::retrieveBean('Users', $userId);
            $targetedEmailType = $user->getPreference('email_link_type');
            $this->originalEmailType[$userId] = [
                'email_link_type' => $targetedEmailType,
            ];

            $this->assertEquals($emailType, $targetedEmailType);
        }
    }
}
