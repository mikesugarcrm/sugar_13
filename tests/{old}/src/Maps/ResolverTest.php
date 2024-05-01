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

namespace Sugarcrm\SugarcrmTests\Maps;

use BeanFactory;
use DBManagerFactory;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Maps\Constants;
use Sugarcrm\Sugarcrm\Maps\GCSClient;
use Sugarcrm\Sugarcrm\Maps\Logger;
use Sugarcrm\Sugarcrm\Maps\Resolver;
use SugarTestAccountUtilities;
use SugarTestHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Maps
 */
class ResolverTest extends TestCase
{
    /**
     * @var mixed[]|mixed
     */
    public $deleteAssets;
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
    public $geocodes = [];
    public $externalSchedulers = [];

    public function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        global $current_user;
        /**
         * @var User
         */
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();

        $this->setLogger();

        $db = DBManagerFactory::getInstance();
        $this->accounts['account0'] = SugarTestAccountUtilities::createAccount('account0');
        $this->accounts['account1'] = SugarTestAccountUtilities::createAccount('account1');

        $this->createGeocodeBean('geocode0', 'account0');
        $this->createGeocodeBean('geocode1', 'account1');
        $this->createExternalScheduler();

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
     * Create demo geocode bean
     *
     * @param String $recordKey
     * @param String $accountKey
     * @return void
     */
    private function createGeocodeBean(string $recordKey, string $accountKey)
    {
        $this->geocodes[$recordKey] = BeanFactory::newBean(Constants::GEOCODE_MODULE);
        $this->geocodes[$recordKey]->new_with_id = true;
        $this->geocodes[$recordKey]->id = $recordKey;
        $this->geocodes[$recordKey]->parent_id = $this->accounts[$accountKey]->id;
        $this->geocodes[$recordKey]->parent_type = $this->accounts[$accountKey]->module_name;
        $this->geocodes[$recordKey]->parent_type = '1';
        $this->geocodes[$recordKey]->status = Constants::GEOCODE_SCHEDULER_STATUS_QUEUED;
        $this->geocodes[$recordKey]->geocoded = false;
        $this->geocodes[$recordKey]->save();

        $this->addBeanToDeleteAssets($this->geocodes[$recordKey]);
    }

    /**
     * Create demo geocode bean
     *
     * @param String $recordKey
     * @param String $accountKey
     * @return void
     */
    private function createExternalScheduler()
    {
        $this->externalSchedulers['external_scheduler0'] = BeanFactory::newBean(Constants::GEOCODE_SCHEDULER_MODULE);
        $this->externalSchedulers['external_scheduler0']->status = Constants::GEOCODE_SCHEDULER_STATUS_QUEUED;
        $this->externalSchedulers['external_scheduler0']->new_with_id = true;
        $this->externalSchedulers['external_scheduler0']->id = 'external_scheduler0';
        $this->externalSchedulers['external_scheduler0']->save();

        $this->addBeanToDeleteAssets($this->externalSchedulers['external_scheduler0']);
    }

    public function tearDown(): void
    {
        global $current_user;
        $current_user = null;

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $this->cleanUp();

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
     * @param string $batchId
     * @param array $demoData
     * @param array $expectedResults
     * @return void
     */
    public function testRun($batchId, $demoData, $expectedResults)
    {
        $expectedResult = hasSystemMapsLicense() ? $expectedResults['hasLicense'] : $expectedResults['noLicense'];

        $gcsClientMock = $this->getCustomMock(
            GCSClient::class,
            ['getData'],
            false
        );

        $gcsClientMock->expects($this->any())
            ->method('getData')
            ->will($this->returnValue([
                'batchId' => $batchId,
                'response' => $demoData,
            ]));

        $resolver = new Resolver();

        TestReflection::setProtectedValue($resolver, 'gcsClient', $gcsClientMock);

        $jobClass = \SugarAutoLoader::customClass(\Sugarcrm\Sugarcrm\Maps\Resolver::class);
        $jobExec = "class::\\{$jobClass}";

        $schedulerJob = \BeanFactory::newBean('SchedulersJobs');
        $schedulerJob->name = 'Maps Resolver';
        $schedulerJob->target = $jobExec;

        $resolver->setJob($schedulerJob);
        $resolver->run(null);

        $externalSchedulerBean = BeanFactory::retrieveBean(
            Constants::GEOCODE_SCHEDULER_MODULE,
            'external_scheduler0',
            ['use_cache' => false]
        );

        $this->assertEquals(
            $expectedResult['expected_processed_entity_success_count'],
            $externalSchedulerBean->processed_entity_success_count
        );

        $this->assertEquals(
            $expectedResult['expected_processed_entity_failed_count'],
            $externalSchedulerBean->processed_entity_failed_count
        );

        $this->assertEquals($expectedResult['expected_status'], $externalSchedulerBean->status);

        $geocodeBean0 = BeanFactory::retrieveBean(
            Constants::GEOCODE_MODULE,
            'geocode0'
        );

        $geocodeBean1 = BeanFactory::retrieveBean(
            Constants::GEOCODE_MODULE,
            'geocode1'
        );

        $this->assertEquals(
            $expectedResult['expected_lat0'],
            $geocodeBean0->latitude
        );

        $this->assertEquals(
            $expectedResult['expected_long0'],
            $geocodeBean0->longitude
        );

        $this->assertEquals(
            $expectedResult['expected_status'],
            $geocodeBean0->status
        );

        $this->assertEquals(
            $expectedResult['expected_lat1'],
            $geocodeBean1->latitude
        );

        $this->assertEquals(
            $expectedResult['expected_long1'],
            $geocodeBean1->longitude
        );

        $this->assertEquals(
            $expectedResult['expected_status'],
            $geocodeBean1->status
        );
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
                'batchId' => 'external_scheduler0',
                [
                    'status' => 'COMPLETED',
                    'data' => [
                        [
                            'sugar_id' => 'geocode0',
                            'sugar_module' => 'Geocode',
                            'lat' => '43.2766227722168',
                            'long' => '-89.3570556640625',
                            'status' => 'COMPLETED',
                            'address_hash' => 'RGVudmVyVVNBNTM1NzFQTkgSUJNIFBhdGg=',
                            'error_message' => '',
                            'postalcode' => '01020',
                            'country' => 'United States',
                        ],
                        [
                            'sugar_id' => 'geocode1',
                            'sugar_module' => 'Geocode',
                            'lat' => '42.9392318725586',
                            'long' => '-75.6200485229492',
                            'status' => 'COMPLETED',
                            'address_hash' => 'U2FsdCBMYWtlIENpdHlVU0ExMzM5Nk5ZNjczMjEgV2VzdCBTaWFtIFN0Lg==',
                            'error_message' => '',
                            'postalcode' => '01020',
                            'country' => 'United States',
                        ],
                    ],
                    'failedEntityCount' => -1,
                    'successEntityCount' => 2,
                ],
                [
                    'hasLicense' => [
                        'expected_processed_entity_success_count' => 2,
                        'expected_processed_entity_failed_count' => -1,
                        'expected_lat0' => '43.2766227722168',
                        'expected_long0' => '-89.3570556640625',
                        'expected_lat1' => '42.9392318725586',
                        'expected_long1' => '-75.6200485229492',
                        'expected_status' => 'COMPLETED',
                    ],
                    'noLicense' => [
                        'expected_processed_entity_success_count' => '',
                        'expected_processed_entity_failed_count' => '',
                        'expected_lat0' => '0',
                        'expected_long0' => '0',
                        'expected_lat1' => '0',
                        'expected_long1' => '0',
                        'expected_status' => 'QUEUED',
                    ],
                ],
            ],
        ];
    }

    /**
     * Tracks which beans were added so that they can be deleted later
     * @param SugarBean $bean
     */
    protected function addBeanToDeleteAssets($bean): void
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
