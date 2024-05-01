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

namespace Sugarcrm\SugarcrmTests\Maps\Queue\QueueManager;

use BeanFactory;
use DBManagerFactory;
use Exception;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\UnknownTypeException;
use PHPUnit\Framework\MockObject\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\DuplicateMethodException;
use PHPUnit\Framework\MockObject\ClassIsFinalException;
use PHPUnit\Framework\MockObject\ClassAlreadyExistsException;
use PHPUnit\Framework\MockObject\OriginalConstructorInvocationRequiredException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\ReflectionException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MethodNameAlreadyConfiguredException;
use PHPUnit\Framework\MockObject\MethodCannotBeConfiguredException;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException as LogInvalidArgumentException;
use ReflectionException as GlobalReflectionException;
use SebastianBergmann\RecursionContext\InvalidArgumentException as RecursionContextInvalidArgumentException;
use SugarApiExceptionNotFound;
use SugarBean;
use Sugarcrm\Sugarcrm\Maps\Constants;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Geocode;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Geocoder;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Container;
use Sugarcrm\Sugarcrm\Maps\Logger;
use Sugarcrm\Sugarcrm\Maps\Queue\Geocode\Consumer;
use Sugarcrm\Sugarcrm\Maps\Queue\QueueManager;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarQueryException;
use SugarTestAccountUtilities;

class QueueManagerTest extends TestCase
{
    /**
     * @var mixed|\Sugarcrm\Sugarcrm\Maps\Logger
     */
    public $logger;
    /**
     * @var array<string, mixed>|mixed
     */
    public $deleteAssets;
    public $accounts = [];
    public $geocodes = [];
    public $initialModuleData = '[]';

    public function setUp(): void
    {
        $db = DBManagerFactory::getInstance();

        $this->accounts['account0'] = SugarTestAccountUtilities::createAccount('', [
            'billing_address_street' => '9 IBM Path',
            'billing_address_city' => 'Los Angeles',
            'billing_address_state' => 'CA',
            'billing_address_postalcode' => '92568',
            'billing_address_country' => 'USA',
        ]);

        $this->accounts['account1'] = SugarTestAccountUtilities::createAccount('', [
            'billing_address_street' => '48920 San Carlos Ave',
            'billing_address_city' => 'Los Angeles',
            'billing_address_state' => 'CA',
            'billing_address_postalcode' => '65645',
            'billing_address_country' => 'USA',
        ]);

        $geocode0 = BeanFactory::newBean('Geocode');
        $geocode0->parent_id = $this->accounts['account0']->id;
        $geocode0->parent_type = 'Accounts';
        $geocode0->save();

        $this->geocodes['geocode0'] = $geocode0;
        $this->addBeanToDeleteAssets($geocode0);

        $geocode1 = BeanFactory::newBean('Geocode');
        $geocode1->parent_id = $this->accounts['account1']->id;
        $geocode1->parent_type = 'Accounts';
        $geocode1->save();

        $this->geocodes['geocode1'] = $geocode1;
        $this->addBeanToDeleteAssets($geocode1);

        $truncateMapsQueue = $db->truncateTableSQL('geocode_queue');
        $truncateGeocode = $db->truncateTableSQL('geocode');

        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateMapsQueue);
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateGeocode);

        // modulesData
        $demoModulesData = [
            'Accounts' => [
                'options' => [],
                'subpanelLayout' => [],
                'mappings' => [
                    'addressLine' => 'billing_address_street',
                    'locality' => 'billing_address_city',
                    'adminDistrict' => 'billing_address_state',
                    'postalCode' => 'billing_address_postalcode',
                    'countryRegion' => 'billing_address_country',
                ],
            ],
        ];

        $encodedDemoModulesData = base64_encode(json_encode($demoModulesData));

        $initialConfigModulesDataQuery = sprintf(
            'SELECT value FROM %s WHERE category=%s AND name=%s',
            'config',
            $db->quoted('maps'),
            $db->quoted('modulesData')
        );

        $resultModulesData = $db->query($initialConfigModulesDataQuery);
        $this->initialModuleData = $db->fetchByAssoc($resultModulesData)['value'];

        $cleanDemoModulesDate = sprintf(
            'UPDATE %s SET value=%s WHERE category=%s AND name=%s',
            'config',
            $db->quoted($encodedDemoModulesData),
            $db->quoted('maps'),
            $db->quoted('modulesData')
        );

        $db->query($cleanDemoModulesDate);
    }

    public function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $db = DBManagerFactory::getInstance();

        $truncate = $db->truncateTableSQL('geocode_queue');
        $truncateGeocode = $db->truncateTableSQL('geocode');
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncate);
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateGeocode);


        $cleanDemoModulesDate = sprintf(
            'UPDATE %s SET value=%s WHERE category=%s AND name=%s',
            'config',
            $db->quoted($this->initialModuleData),
            $db->quoted('maps'),
            $db->quoted('modulesData')
        );

        $db->query($cleanDemoModulesDate);

        $this->cleanUp();
    }

    /**
     * @covers ::queueModules
     *
     * Test for queueModules function
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
     * @throws MethodNameAlreadyConfiguredException
     * @throws MethodCannotBeConfiguredException
     * @throws GlobalReflectionException
     * @throws Exception
     * @throws DBALException
     * @throws ExpectationFailedException
     * @throws RecursionContextInvalidArgumentException
     */
    public function testQueueModules()
    {
        $this->setLogger();

        $clientMock = $this->getCustomMock(Geocode::class, ['getEnabledModules'], false, [$this->logger]);
        $clientMock->expects($this->any())
            ->method('getEnabledModules')
            ->will($this->returnValue(['Accounts']));

        $geocoderMock = $this->getCustomMock(Geocoder::class, ['getGeocodingMapping'], true, [], true);
        $geocoderMock->expects($this->any())
            ->method('getGeocodingMapping')
            ->will($this->returnValue([
                'locality' => 'id',
                'addressLine' => 'billing_address_street',
                'adminDistrict' => 'billing_address_state',
                'postalCode' => 'billing_address_postalcode',
                'countryRegion' => 'billing_address_country',
            ]));

        $container = Container::getInstance();

        TestReflection::setProtectedValue($container, 'client', $clientMock);
        TestReflection::setProtectedValue($container, 'geocoder', $geocoderMock);

        $queueManager = new QueueManager(Container::class);
        $queueManager->queueModules(['Accounts'], 2);

        $queuedRecords = $this->countMapsQueueTable();
        $this->assertEquals($queuedRecords, 2);
    }

    /**
     * @covers ::getQueuedModules
     * @dataProvider getQueueModulesProvider
     *
     * @param array $dataSet
     * @param array $expected
     *
     * @throws Exception
     * @throws DBALException
     * @throws ExpectationFailedException
     * @throws RecursionContextInvalidArgumentException
     */
    public function testGetQueueModules(array $dataSet, array $expected)
    {
        $db = DBManagerFactory::getInstance();

        foreach ($dataSet as $key => $record) {
            $sql = sprintf(
                'INSERT INTO %s (id, bean_id, geocode_id, bean_module, date_modified, date_created)
                VALUES(%s, %s, %s, %s, %s, %s)',
                'geocode_queue',
                $db->getGuidSQL(),
                $db->quoted($record['bean_id']),
                $db->quoted($record['geocode_id']),
                $db->quoted($record['bean_module']),
                $db->now(),
                $db->now()
            );

            $db->getConnection()
                ->executeStatement($sql);
        }

        $queueManager = new QueueManager(Container::class);
        $queuedModules = $queueManager->getQueuedModules();

        $this->assertEqualsCanonicalizing($expected, $queuedModules);
    }

    /**
     * getQueueModulesProvider function
     *
     * demo data for testGetQueueModules
     *
     * @return array
     */
    public function getQueueModulesProvider()
    {
        $account0 = SugarTestAccountUtilities::createAccount('', [
            'billing_address_street' => '9 IBM Path',
            'billing_address_city' => 'Los Angeles',
            'billing_address_state' => 'CA',
            'billing_address_postalcode' => '92568',
            'billing_address_country' => 'USA',
        ]);

        $account1 = SugarTestAccountUtilities::createAccount('', [
            'billing_address_street' => '48920 San Carlos Ave',
            'billing_address_city' => 'Los Angeles',
            'billing_address_state' => 'CA',
            'billing_address_postalcode' => '65645',
            'billing_address_country' => 'USA',
        ]);

        $geocode0 = BeanFactory::newBean('Geocode');
        $geocode0->parent_id = $account0->id;
        $geocode0->parent_type = 'Accounts';
        $geocode0->save();

        $this->addBeanToDeleteAssets($geocode0);

        $geocode1 = BeanFactory::newBean('Geocode');
        $geocode1->parent_id = $account1->id;
        $geocode1->parent_type = 'Accounts';
        $geocode1->save();

        $this->addBeanToDeleteAssets($geocode1);

        return [
            [
                'dataSet' => [
                    [
                        'bean_id' => $account0->id,
                        'geocode_id' => $geocode0->id,
                        'bean_module' => 'Accounts',
                    ],
                    [
                        'bean_id' => $account1->id,
                        'geocode_id' => $geocode1->id,
                        'bean_module' => 'Accounts',
                    ],
                ],
                'expected' => [
                    'Accounts',
                ],
            ],
        ];
    }

    /**
     * @covers ::createConsumer
     * @dataProvider createConsumerProvider
     *
     * @param String $module
     *
     * @throws SugarQueryException
     * @throws DoctrineDBALException
     * @throws LogInvalidArgumentException
     * @throws DBALException
     */
    public function testCreateConsumer(string $module, int $expected)
    {
        $db = DBManagerFactory::getInstance();
        $jobTarget = 'class::\Sugarcrm\Sugarcrm\Maps\Queue\Geocode\Consumer';

        $sql = sprintf(
            'DELETE FROM %s WHERE target=%s',
            \BeanFactory::newBean('SchedulersJobs')->table_name,
            $db->quoted($jobTarget)
        );

        $conn = $db->getConnection();
        $conn->executeStatement($sql);

        //create the consumer job
        $count = $this->createConsumerJob($module);

        //verfiy it has been created
        $this->assertEquals($expected, $count);

        //try to create another one
        $count = $this->createConsumerJob($module);

        //make sure a new one was not created
        $this->assertEquals($expected, $count);

        //cleanup
        $conn->executeStatement($sql);
    }

    /**
     * createConsumerProvider function
     *
     * demo data for testCreateConsumer
     *
     * @return array
     */
    public function createConsumerProvider()
    {
        return [
            [
                'module' => 'Accounts',
                'expected' => 1,
            ],
        ];
    }

    /**
     * @param string $module
     *
     * @throws SugarQueryException
     * @throws DoctrineDBALException
     * @throws LogInvalidArgumentException
     * @throws DBALException
     */
    private function createConsumerJob(string $module): int
    {
        $queueManager = new QueueManager(Container::class);
        $queueManager->createConsumer($module, Consumer::class);

        $countQuery = new \SugarQuery();
        $countQuery->select('id');
        $countQuery->from(\BeanFactory::newBean('SchedulersJobs'))
            ->where()
            ->equals('target', 'class::\Sugarcrm\Sugarcrm\Maps\Queue\Geocode\Consumer');

        $count = count($countQuery->execute());

        return $count;
    }

    /**
     * @covers ::consumeModuleFromQueue
     *
     * @return void
     * @throws Exception
     * @throws DBALException
     * @throws ExpectationFailedException
     * @throws RecursionContextInvalidArgumentException
     * @throws SugarQueryException
     * @throws DoctrineDBALException
     * @throws SugarApiExceptionNotFound
     */
    public function testConsumeModuleFromQueue()
    {
        $this->setLogger();

        $this->demoDataConsumeModuleFromQueue();

        $initialCount = $this->countGeocodeScheduler();

        $clientMock = $this->getCustomMock(
            Geocode::class,
            ['sendRecordsToGCS'],
            false,
            [$this->logger]
        );


        $geocoderMock = $this->getCustomMock(Geocoder::class, ['getGeocodingMapping'], true, [], true);
        $geocoderMock->expects($this->any())
            ->method('getGeocodingMapping')
            ->will($this->returnValue([
                'addressLine' => 'billing_address_street',
                'locality' => 'billing_address_city',
                'adminDistrict' => 'billing_address_state',
                'postalCode' => 'billing_address_postalcode',
                'countryRegion' => 'billing_address_country',
            ]));

        $container = Container::getInstance();

        TestReflection::setProtectedValue($container, 'client', $clientMock);
        TestReflection::setProtectedValue($container, 'geocoder', $geocoderMock);

        $queueManager = new QueueManager(Container::class);
        $queueManager->consumeModuleFromQueue('Accounts');

        $finalCount = $this->countGeocodeScheduler();

        $this->deleteLastGeocodeSchedulerRecord();

        $this->assertEquals($initialCount + 1, $finalCount);
    }

    /**
     * Get records number from geocode_queue table
     *
     * @return integer
     *
     * @throws Exception
     * @throws DBALException
     */
    private function countMapsQueueTable()
    {
        $countQuery = <<<EOQ
            SELECT
                count(*)
            FROM
                geocode_queue
EOQ;
        $db = DBManagerFactory::getInstance();

        $count = $db->getConnection()
            ->executeQuery($countQuery)
            ->fetchOne();

        return $count;
    }

    /**
     * Get records number from geocode_job table
     *
     * @return integer
     *
     * @throws Exception
     * @throws DBALException
     */
    private function countGeocodeScheduler()
    {
        $countQuery = <<<EOQ
            SELECT
                count(*)
            FROM
                geocode_job
EOQ;
        $db = DBManagerFactory::getInstance();

        $count = $db->getConnection()
            ->executeQuery($countQuery)
            ->fetchOne();

        return $count;
    }

    /**
     * Delete last created GeocodeScheduler record
     *
     * @return void
     * @throws Exception
     * @throws DoctrineDBALException
     */
    private function deleteLastGeocodeSchedulerRecord()
    {
        $countQuery = new \SugarQuery();
        $countQuery->select('id');
        $countQuery->from(\BeanFactory::newBean(Constants::GEOCODE_SCHEDULER_MODULE))
            ->orderBy('date_entered')
            ->limit(1);

        $result = $countQuery->execute();

        if (!$result || !is_array($result) || count($result) < 1) {
            return;
        }

        $id = $result[0]['id'];

        $db = DBManagerFactory::getInstance();

        $deleteQuery = sprintf(
            'DELETE FROM %s WHERE id=%s',
            'geocode_job',
            $db->quoted($id)
        );

        $db->getConnection()
            ->executeStatement($deleteQuery);
    }

    /**
     * Populate geocode_queue with demo data
     */
    private function demoDataConsumeModuleFromQueue()
    {
        $db = DBManagerFactory::getInstance();

        foreach ($this->geocodes as $geocode) {
            $sql = sprintf(
                'INSERT INTO %s (id, bean_id, geocode_id, bean_module, date_modified, date_created)
                VALUES(%s, %s, %s, %s, %s, %s)',
                'geocode_queue',
                $db->getGuidSQL(),
                $db->quoted($geocode->parent_id),
                $db->quoted($geocode->id),
                $db->quoted($geocode->parent_type),
                $db->now(),
                $db->now()
            );

            $db->getConnection()
                ->executeStatement($sql);
        }
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
        array  $constructorArgs = [],
        bool   $onlyMethods = false
    ) {

        $mockObject = $this->getMockBuilder($className);

        if ($disableOriginalConstructor) {
            $mockObject->disableOriginalConstructor();
        } else {
            $mockObject->setConstructorArgs($constructorArgs);
        }

        if ($onlyMethods) {
            $mockObject->onlyMethods($methods);
        } else {
            $mockObject->setMethods($methods);
        }

        return $mockObject->getMock();
    }

    /**
     * Tracks which beans were added so that they can be deleted later
     * @param SugarBean $bean
     */
    protected function addBeanToDeleteAssets(SugarBean $bean): void
    {
        $this->deleteAssets[$bean->getTableName()][] = $bean->id;
    }

    /**
     * cleanUp function
     *
     * Remove demo data
     *
     * @return void
     */
    protected function cleanUp(): void
    {
        foreach ($this->deleteAssets as $table => $ids) {
            $qb = \DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
            $qb->delete($table)->where(
                $qb->expr()->in(
                    'id',
                    $qb->createPositionalParameter($ids, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                )
            )->execute();
        }
    }
}
