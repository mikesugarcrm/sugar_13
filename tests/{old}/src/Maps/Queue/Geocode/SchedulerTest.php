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

namespace Sugarcrm\SugarcrmTestsUnit\Maps\Queue\Geocode;

use DBManagerFactory;
use Doctrine\DBAL\DBALException;
use Sugarcrm\Sugarcrm\Maps\Queue\Geocode\Scheduler;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Container;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Engine;
use Sugarcrm\Sugarcrm\Maps\Logger;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarTestAccountUtilities;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Maps\Queue\Geocode\Scheduler
 */
class SchedulerTest extends TestCase
{
    /**
     * @var \Sugarcrm\Sugarcrm\Maps\Logger
     */
    public $logger;
    /**
     * Demo data
     *
     * @var array
     */
    public $accounts = [];

    public function setUp(): void
    {
        $this->setLogger();

        $db = DBManagerFactory::getInstance();
        $this->accounts['account0'] = SugarTestAccountUtilities::createAccount();
        $this->accounts['account1'] = SugarTestAccountUtilities::createAccount();

        $truncateMapsQueue = $db->truncateTableSQL('geocode_queue');
        $truncateJobQueue = $db->truncateTableSQL('job_queue');
        $truncateGeocode = $db->truncateTableSQL('geocode');

        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateMapsQueue);
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateJobQueue);
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateGeocode);
    }

    public function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $db = DBManagerFactory::getInstance();

        $truncateMapsQueue = $db->truncateTableSQL('geocode_queue');
        $truncateJobQueue = $db->truncateTableSQL('job_queue');
        $truncateGeocode = $db->truncateTableSQL('geocode');

        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateMapsQueue);
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateJobQueue);
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateGeocode);
    }

    /**
     * @dataProvider runProvider
     *
     * @param array $queuedModules
     * @param array $expects
     *
     * @throws DBALException
     */
    public function testRun($queuedModules, $expects)
    {
        $expected = hasSystemMapsLicense() ? $expects['hasLicense'] : $expects['noLicense'];

        $engineMock = $this->getCustomMock(
            Engine::class,
            ['isConfigured', 'getQueuedModules', 'queueModules', 'getContainer'],
            false
        );

        $engineMock->expects($this->any())
            ->method('isConfigured')
            ->will($this->returnValue(true));

        $engineMock->expects($this->any())
            ->method('getQueuedModules')
            ->will($this->returnValue($queuedModules));

        $engineMock->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue(Container::getInstance()));

        $batch = 2;

        $engineMock->expects($this->any())
            ->method('queueModules')
            ->willReturnCallback(function () use ($queuedModules, $batch, $engineMock) {
                $engineMock->getContainer()->queueManager->queueModules($queuedModules, $batch);
            });

        $scheduler = new Scheduler();

        TestReflection::setProtectedValue($scheduler, 'engine', $engineMock);

        $jobClass = \SugarAutoLoader::customClass(\Sugarcrm\Sugarcrm\Maps\Queue\Geocode\Scheduler::class);
        $jobExec = "class::\\{$jobClass}";

        $schedulerJob = \BeanFactory::newBean('SchedulersJobs');
        $schedulerJob->name = 'Maps Records Geocoder';
        $schedulerJob->target = $jobExec;

        $scheduler->setJob($schedulerJob);
        $result = $scheduler->run(null);

        $this->assertEquals(true, $result);

        $countQuery = new \SugarQuery();
        $countQuery->select('id');
        $countQuery->from(\BeanFactory::newBean('SchedulersJobs'))
            ->where()
            ->queryAnd()
            ->equals('target', $jobExec)
            ->equals('status', 'done')
            ->equals('resolution', 'success');

        $count = count($countQuery->execute());

        $this->assertEquals($expected, $count);
    }

    /**
     * runProvider function
     *
     * demo data for testRun
     *
     * @return array
     */
    public function runProvider()
    {
        return [
            [
                'queuedModules' => [
                    'Accounts',
                ],
                'expected' => [
                    'hasLicense' => 1,
                    'noLicense' => 0,
                ],
            ],
        ];
    }

    /**
     * set logger
     */
    protected function setLogger()
    {
        $logMgr = \LoggerManager::getLogger();
        // don't record anything in the log
        $logMgr->setLevel('off');
        $this->logger = new Logger($logMgr);
    }

    /**
     *
     * @param string $className
     * @param array|null $methods
     * @param bool $disableOriginalConstructor
     * @param array $constructorArgs
     *
     * @return MockObject
     *
     * @throws UnknownTypeException
     * @throws InvalidMethodNameException
     * @throws DuplicateMethodException
     * @throws ClassIsFinalException
     * @throws ClassAlreadyExistsException
     * @throws OriginalConstructorInvocationRequiredException
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    protected function getCustomMock(
        string $className,
        array  $methods = null,
        bool   $disableOriginalConstructor = true,
        array  $constructorArgs = []
    ) {

        $mockObject = $this->getMockBuilder($className);

        if ($disableOriginalConstructor) {
            $mockObject->disableOriginalConstructor();
        } else {
            $mockObject->setConstructorArgs($constructorArgs);
        }

        $mockObject->setMethods($methods);

        return $mockObject->getMock();
    }
}
