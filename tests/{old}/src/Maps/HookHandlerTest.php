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
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Maps\HookHandler;
use SugarTestHelper;

/**
 * @coversClass src\Maps\HookHandler
 */
class HookHandlerTest extends TestCase
{
    private $testAccountBean = null;
    private $testGeocodeBean = null;
    private $deleteAssets = [];

    public function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        global $current_user;
        /**
         * @var User
         */
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();

        $this->testAccountBean = BeanFactory::newBean('Accounts');
        $this->testAccountBean->name = 'Test';
        $this->testAccountBean->billing_address_street = 'Street';
        $this->testAccountBean->billing_address_city = 'City';
        $this->testAccountBean->billing_address_state = 'State';
        $this->testAccountBean->billing_address_postalcode = 'Postalcode';
        $this->testAccountBean->billing_address_country = 'Country';
        $this->testAccountBean->save();

        $this->addBeanToDeleteAssets($this->testAccountBean);

        $this->testGeocodeBean = BeanFactory::newBean('Geocode');
        $this->testGeocodeBean->parent_id = $this->testAccountBean->id;
        $this->testGeocodeBean->parent_type = 'Accounts';
        $this->testGeocodeBean->geocoded = true;
        $this->testGeocodeBean->save();

        $this->addBeanToDeleteAssets($this->testGeocodeBean);
    }

    /**
     * @covers       geocode
     * @dataProvider geocodeProvider
     *
     * @param array $expects
     * @param array $args
     *
     * @return void
     */
    public function testGeocode($expects, $args)
    {
        $expected = hasMapsLicense() ? $expects['hasLicense'] : $expects['noLicense'];

        $mock = $this->getCustomMock(
            HookHandler::class,
            ['getMapsModuleMappings', 'getGeocodeBean', 'isMappable'],
            true
        );

        $mock->expects($this->any())
            ->method('getMapsModuleMappings')
            ->will($this->returnValue([
                'addressLine' => 'billing_address_street',
                'locality' => 'billing_address_city',
                'adminDistrict' => 'billing_address_state',
                'postalCode' => 'billing_address_postalcode',
                'countryRegion' => 'billing_address_country',
            ]));

        $mock->expects($this->any())
            ->method('getGeocodeBean')
            ->will($this->returnValue($this->testGeocodeBean));

        $mock->expects($this->any())
            ->method('isMappable')
            ->will($this->returnValue(true));

        $mock->geocode($this->testAccountBean, 'before_save', $args);

        $geocodeBean = BeanFactory::retrieveBean('Geocode', $this->testGeocodeBean->id, ['use_cache' => false]);

        $this->assertEquals($geocodeBean->geocoded, $expected);
    }

    /**
     *  Provider for geocode
     */
    public function geocodeProvider()
    {
        return [
            [
                [
                    'hasLicense' => 1,
                    'noLicense' => 1,
                ],
                [
                    'dataChanges' => [
                        'name' => 'New Name',
                    ],
                    'isUpdate' => true,
                ],
            ],
            [
                [
                    'hasLicense' => 0,
                    'noLicense' => 1,
                ],
                [
                    'dataChanges' => [
                        'billing_address_city' => 'New City',
                    ],
                    'isUpdate' => true,
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

    public function tearDown(): void
    {
        global $current_user;
        $current_user = null;

        $this->cleanUp();
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
}
