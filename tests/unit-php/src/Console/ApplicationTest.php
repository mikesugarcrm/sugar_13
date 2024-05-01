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

namespace Sugarcrm\SugarcrmTestsUnit\Console;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Console\Application;
use Sugarcrm\Sugarcrm\Console\Command\Api\IdmModeManageCommand;
use Sugarcrm\Sugarcrm\Console\CommandRegistry\CommandRegistry;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Console\RebuildCommand;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Console\StatusCommand;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Console\TeamSetPruneBackupCommand;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Console\TeamSetPrunePruneCommand;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Console\TeamSetPruneRestoreFromBackupCommand;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Console\TeamSetPruneScanCommand;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Console\TeamSetPruneSqlCommand;
use Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\ApplicationTestCommandA;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Application
 */
class ApplicationTest extends TestCase
{
    /**
     * @covers ::create
     * @dataProvider providerTestAvailableStockCommands
     */
    public function testAvailableStockCommands($mode, array $expected)
    {
        $app = Application::create($mode);
        $commands = $app->all();

        foreach ($expected as $name => $class) {
            $this->assertArrayHasKey($name, $commands, "$name is not created");
            $this->assertInstanceOf($class, $commands[$name]);
        }
    }

    public function providerTestAvailableStockCommands()
    {
        $ns = 'Sugarcrm\Sugarcrm\Console\Command';
        return [
            [
                CommandRegistry::MODE_STANDALONE,
                [
                    'help' => \Symfony\Component\Console\Command\HelpCommand::class,
                    'list' => \Symfony\Component\Console\Command\ListCommand::class,
                ],
            ],
            [
                CommandRegistry::MODE_INSTANCE,
                [
                    'help' => \Symfony\Component\Console\Command\HelpCommand::class,
                    'list' => \Symfony\Component\Console\Command\ListCommand::class,
                    'elastic:indices' => $ns . '\Api\ElasticsearchIndicesCommand',
                    'elastic:queue' => $ns . '\Api\ElasticsearchQueueCommand',
                    'elastic:queue_cleanup' => $ns . '\Elasticsearch\CleanupQueueCommand',
                    'elastic:routing' => $ns . '\Api\ElasticsearchRoutingCommand',
                    'elastic:refresh_status' => $ns . '\Api\ElasticsearchRefreshStatusCommand',
                    'elastic:refresh_enable' => $ns . '\Api\ElasticsearchRefreshEnableCommand',
                    'elastic:refresh_trigger' => $ns . '\Api\ElasticsearchRefreshTriggerCommand',
                    'elastic:replicas_status' => $ns . '\Api\ElasticsearchReplicasStatusCommand',
                    'elastic:replicas_enable' => $ns . '\Api\ElasticsearchReplicasEnableCommand',
                    'elastic:explain' => $ns . '\Elasticsearch\ExplainCommand',
                    'search:fields' => $ns . '\Api\SearchFieldsCommand',
                    'search:reindex' => $ns . '\Api\SearchReindexCommand',
                    'search:status' => $ns . '\Api\SearchStatusCommand',
                    'search:module' => $ns . '\Elasticsearch\ModuleCommand',
                    'search:silent_reindex' => $ns . '\Elasticsearch\SilentReindexCommand',
                    'search:silent_reindex_mp' => $ns . '\Elasticsearch\SilentReindexMultiProcessCommand',
                    'password:config' => $ns . '\Password\PasswordConfigCommand',
                    'password:reset' => $ns . '\Password\PasswordResetCommand',
                    'team-security:rebuild' => RebuildCommand::class,
                    'team-security:status' => StatusCommand::class,
                    'teamset:prune' => TeamSetPrunePruneCommand::class,
                    'teamset:restore_from_backup' => TeamSetPruneRestoreFromBackupCommand::class,
                    'teamset:backup' => TeamSetPruneBackupCommand::class,
                    'teamset:scan' => TeamSetPruneScanCommand::class,
                    'teamset:sql' => TeamSetPruneSqlCommand::class,
                    'idm-mode:manage' => IdmModeManageCommand::class,
                ],
            ],
        ];
    }

    /**
     * @covers ::getDefaultInputDefinition
     */
    public function testProfileInputDefinition()
    {
        $app = new Application(new CommandRegistry(), true);
        $this->assertTrue($app->getDefinition()->hasOption('profile'));
    }

    /**
     * @covers ::__construct
     * @covers ::doRun
     */
    public function testDoRun()
    {
        $app = new Application();
        $app->add(new ApplicationTestCommandA());
        $app->setAutoExit(false);

        $this->assertSame('SugarCRM Console', $app->getName());

        $tester = new ApplicationTester($app);

        // regular execution
        $tester->run(['command' => 'apptest:A']);
        $this->assertEquals(
            'Success Application Test A' . PHP_EOL,
            $tester->getDisplay()
        );

        // execution with profiling
        $tester->run(['command' => 'apptest:A', '--profile' => true]);
        $this->assertMatchesRegularExpression(
            '/^Success Application Test A\n\nMemory usage: (.*) MB \(peak: (.*) MB\), time: (.*)s\n$/',
            $tester->getDisplay()
        );
    }

    /**
     * @covers ::getSugarVersion
     */
    public function testGetSugarVersion()
    {
        // make a backup of the current file
        $sugarVersionFile = SUGAR_BASE_DIR . '/sugar_version.php';
        $backupFile = SUGAR_BASE_DIR . '/sugar_version.tests.unit-php';
        copy($sugarVersionFile, $backupFile);

        // version from source tree
        $this->setupSugarVersionFixture('sugar_version_source');
        $app = new Application();
        $this->assertEquals(
            '[standalone mode]',
            $app->getVersion(),
            'Expecting standalone mode for source base sugar_version'
        );

        // version from installed sugar
        $this->setupSugarVersionFixture('sugar_version_installed');
        $app = new Application();
        $this->assertEquals(
            '7.7.0.0-ULT-1234',
            $app->getVersion(),
            'Expecting actual version number from built/installed system'
        );

        // corrupt sugar_version file
        $this->setupSugarVersionFixture('sugar_version_corrupt');
        $app = new Application();
        $this->assertEquals(
            '[standalone mode]',
            $app->getVersion(),
            'Expecting standalone mode for corrupt sugar_version'
        );

        // missing sugar version file (shouldnt happen, but got it covered)
        unlink($sugarVersionFile);
        $app = new Application();
        $this->assertEquals(
            '[standalone mode]',
            $app->getVersion(),
            'Expecting standalone mode for missing sugar_version'
        );

        // restore original version file
        copy($backupFile, $sugarVersionFile);
        unlink($backupFile);
    }

    /**
     * Setup sugar_verion.php fixture
     * @param string $file
     */
    protected function setupSugarVersionFixture($file)
    {
        $file = __DIR__ . '/Fixtures/' . $file . '.txt';
        copy($file, SUGAR_BASE_DIR . '/sugar_version.php');
    }

    /**
     * @covers ::setMode
     * @covers ::getMode
     */
    public function testSetGetMode()
    {
        $app = new Application();
        $this->assertSame('', $app->getMode());
        $app->setMode('yeaha');
        $this->assertSame('yeaha', $app->getMode());
    }
}
