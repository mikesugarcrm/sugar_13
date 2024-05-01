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

use BeanFactory;
use DBManagerFactory;
use PHPUnit\Framework\TestCase;
use SugarAutoLoader;
use Sugarcrm\Sugarcrm\Maps\Engine\Engine;
use Sugarcrm\Sugarcrm\Maps\Queue\Geocode\Consumer;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarQuery;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Maps\Queue\Scheduler
 */
class ConsumerTest extends TestCase
{
    /**
     * Demo data
     *
     * @var array
     */
    public $records = [];

    public function setUp(): void
    {
        $db = DBManagerFactory::getInstance();
        $truncateJobQueue = $db->truncateTableSQL('job_queue');

        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateJobQueue);
    }

    public function tearDown(): void
    {

        $db = DBManagerFactory::getInstance();
        $truncateJobQueue = $db->truncateTableSQL('job_queue');

        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncateJobQueue);
    }

    /**
     * @covers ::getCoordsByAddress
     * @dataProvider runProvider
     */
    public function testRun($module, $expected)
    {
        $engineMock = $this->getCustomMock(
            Engine::class,
            ['isConfigured', 'consumeModuleFromQueue'],
            false
        );

        $engineMock->expects($this->any())
            ->method('isConfigured')
            ->will($this->returnValue(true));


        $engineMock->expects($this->any())
            ->method('consumeModuleFromQueue')
            ->with($this->isType('string'))
            ->will($this->returnValue([true, true, 1, '']));

        $consumer = new Consumer();

        TestReflection::setProtectedValue($consumer, 'engine', $engineMock);

        $jobClass = SugarAutoLoader::customClass(Consumer::class);
        $jobExec = "class::\\{$jobClass}";

        $schedulerJob = BeanFactory::newBean('SchedulersJobs');
        $schedulerJob->name = 'Maps Records Geocoder';
        $schedulerJob->target = $jobExec;

        $consumer->setJob($schedulerJob);
        $result = $consumer->run($module);

        $this->assertEquals(true, $result);

        $countQuery = new SugarQuery();
        $countQuery->select('id');
        $countQuery->from(BeanFactory::newBean('SchedulersJobs'))
            ->where()
            ->queryAnd()
            ->equals('target', $jobExec)
            ->equals('status', $expected['status'])
            ->equals('resolution', $expected['resolution']);

        $count = count($countQuery->execute());

        $this->assertEquals(1, $count);
    }

    /**
     * Data provider for testRun
     */
    public function runProvider()
    {
        return [
            [
                'Accounts',
                [
                    'status' => 'done',
                    'resolution' => 'success',
                ],
            ],
        ];
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
