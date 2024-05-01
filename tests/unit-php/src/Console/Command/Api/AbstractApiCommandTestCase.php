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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Api Command Test Case
 */
abstract class AbstractApiCommandTestCase extends TestCase
{
    /**
     * @var string
     */
    protected static $fixturePath;

    /**
     * Command class name to be tested
     * @var string
     */
    protected $commandClass;

    /**
     * \SugarApi class name
     * @var string
     */
    protected $apiClass;

    /**
     * Method to be mocked on \SugarApi
     * @var string
     */
    protected $apiMethod;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$fixturePath = __DIR__ . '/../../Fixtures/Api/';
    }

    /**
     * @covers ::execute
     * @dataProvider providerTestExecuteCommand
     * @param mixed $data Data to be returned by mocked \SugarApi method
     * @param array $options Command input options
     * @param mixed $expected Expected output value. If the value of this
     *              parameters ends in '.txt' then the actual output will
     *              be compared against the fixture .txt file.
     * @param integer $exit Expected exit code
     */
    public function testExecuteCommand($data, array $options, $expected, $exit)
    {
        $api = $this->getApiMock($this->apiClass, $this->apiMethod, $data);
        $command = $this->getApiCommandMock($this->commandClass, $api);
        $input = array_merge(['command' => $command->getName()], $options);

        $tester = new CommandTester($command);
        $tester->execute($input);

        // output test
        if (is_string($expected) && strpos($expected, '.txt')) {
            $file = self::$fixturePath . $expected;
            $this->assertStringEqualsFile($file, $tester->getDisplay(true));
        } else {
            $this->assertEquals($expected . PHP_EOL, $tester->getDisplay(true));
        }

        // exit code test
        $this->assertSame($exit, $tester->getStatusCode());
    }

    /**
     * Data provider for testExecuteCommand
     * @return array
     */
    abstract public function providerTestExecuteCommand();

    /**
     * Get command mock for API based commands
     * @param string $className Command class name
     * @param \SugarApi $api
     * @return Command
     */
    protected function getApiCommandMock($className, \SugarApi $api)
    {
        $service = $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->getMock();

        $cmd = $this->getMockBuilder($className)
            ->setMethods(['getService', 'getApi'])
            ->getMock();

        $cmd->expects($this->any())
            ->method('getService')
            ->will($this->returnValue($service));

        $cmd->expects($this->any())
            ->method('getApi')
            ->will($this->returnValue($api));

        $cmd->setApplication(new Application());

        return $cmd;
    }

    /**
     * Get mocked API object
     * @param string $className SugarAPI class name
     * @param string $method Stubbed method
     * @param mixed $return Return value for stubbed method
     * @return \SugarApi
     */
    protected function getApiMock($className, $method, $return)
    {
        $api = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods([$method])
            ->getMock();

        $api->expects($this->any())
            ->method($method)
            ->will($this->returnValue($return));

        return $api;
    }
}
