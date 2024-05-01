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

class SugarJobDataArchiverTest extends TestCase
{
    /**
     * @var \Account
     */
    public $ac1;
    /**
     * @var \Account
     */
    public $ac2;
    /**
     * @var \Account
     */
    public $ac3;
    /**
     * @var \Account
     */
    public $ac4;
    private $ar_ids = [];
    private static $db;

    public static function setUpBeforeClass(): void
    {
        static::$db = \DBManagerFactory::getInstance();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
    }

    public static function tearDownAfterClass(): void
    {
        $conn = \DBManagerFactory::getConnection();
        $sm = $conn->getSchemaManager();
        if (static::$db->tableExists('accounts_archive')) {
            $sm->dropTable('accounts_archive');
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        // Create fake testing accounts
        $this->ac1 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account']);
        $this->ac2 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account']);
        $this->ac3 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account']);
        $this->ac4 = SugarTestAccountUtilities::createAccount('', ['name' => 'Test Account']);
    }

    public function tearDown(): void
    {
        SugarTestDataArchiverUtilities::removeAllCreatedArchivers();
    }

    /**
     * @covers SugarJobDataArchiver
     */
    public function testCreateDataArchiverJob(): void
    {
        SugarAutoLoader::load('modules/DataArchiver/clients/base/api/DataArchiverFilterApi.php');
        $user = (BeanFactory::newBean('Users'))->getSystemUser();

        $values = [
            'name' => 'Test',
            'filter_module_name' => 'Accounts',
            'filter_def' => '[{"name":{"$starts":"Test"}}]',
            'process_type' => 'archive',
        ];

        $ar = SugarTestDataArchiverUtilities::createDataArchiver('', $values);
        $this->ar_ids[] = $ar->id;


        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobDataArchiver',
            'class::SugarJobDataArchiver',
            '',
            $user
        );

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status);
    }
}
