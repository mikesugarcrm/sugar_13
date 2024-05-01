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

namespace Sugarcrm\SugarcrmTestsUnit\Maps\Engine\Geocode;

use BeanFactory;
use DBManagerFactory;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\ClassAlreadyExistsException;
use PHPUnit\Framework\MockObject\ClassIsFinalException;
use PHPUnit\Framework\MockObject\DuplicateMethodException;
use PHPUnit\Framework\MockObject\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\OriginalConstructorInvocationRequiredException;
use PHPUnit\Framework\MockObject\ReflectionException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\UnknownTypeException;
use PHPUnit\Framework\TestCase;
use ReflectionException as GlobalReflectionException;
use SugarBean;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Geocode;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Container;
use Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Geocoder;
use Sugarcrm\Sugarcrm\Maps\Logger;
use SugarTestAccountUtilities;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Maps\Engine\Geocode\Geocoder.php
 */
class GeocoderTest extends TestCase
{
    protected $logger;

    /**
     * List of items to delete at the end of the test
     * @var array
     */
    protected $deleteAssets = [];

    /**
     * Demo data
     *
     * @var SugarBean
     */
    public $account = null;

    public function setUp(): void
    {
        $this->setLogger();

        $db = DBManagerFactory::getInstance();
        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown(): void
    {
        $this->cleanUp();

        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * @covers ::geocodeBean
     * @dataProvider runGeocodeBean
     *
     * @param mixed $coords
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
     * @throws GlobalReflectionException
     */
    public function testGeocodeBean($coords, $expected)
    {
        $clientMock = $this->getCustomMock(
            Geocode::class,
            ['getCoordsByAddress']
        );

        $clientMock->expects($this->any())
            ->method('getCoordsByAddress')
            ->will($this->returnValue($coords));

        $geocodeBean = BeanFactory::newBean('Geocode');
        $geocodeBean->save();

        $this->addBeanToDeleteAssets($geocodeBean);

        $container = Container::getInstance();

        TestReflection::setProtectedValue($container, 'client', $clientMock);

        $geocoder = Geocoder::getInstance();

        TestReflection::setProtectedValue($geocoder, 'container', $container);

        $geocoder->geocodeBean($this->account, $geocodeBean);

        $this->assertEquals($expected['lat'], $geocodeBean->latitude);
        $this->assertEquals($expected['long'], $geocodeBean->longitude);
        $this->assertEquals($expected['geocoded'], $geocodeBean->geocoded);
    }

    /**
     * Data GeocodeBean for testRun
     */
    public function runGeocodeBean()
    {
        return [
            [
                [
                    'lat' => '132.32',
                    'long' => '-180.32',
                ],
                [
                    'lat' => '132.32',
                    'long' => '-180.32',
                    'geocoded' => true,
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
