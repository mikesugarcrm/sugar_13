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

namespace Sugarcrm\SugarcrmTestsUnit\inc\api;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class RestServiceTest
 * @coversDefaultClass \RestService
 */
class RestServiceTest extends TestCase
{
    public const SITE_URL = 'sugarcrm.com/';

    protected function setUp(): void
    {
        // pre-setting
        $_REQUEST['__sugar_url'] = 'sugar.com';
    }

    /**
     * @covers ::getResourceURI
     * @covers ::setResourceURIBase
     *
     * @dataProvider providerGetResourceURI
     */
    public function testGetResourceURI($resource, $hasRoute, $options, $requestUrl, $scriptName, $expected)
    {

        $_SERVER['REQUEST_URI'] = $requestUrl;
        $_SERVER['SCRIPT_NAME'] = $scriptName;

        $requestMock = $this->createPartialMock('RestRequest', ['getVersion', 'getResourceURIBase']);
        $requestMock->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('11.2'));
        if ($options['relative'] == true) {
            $requestMock->expects($this->any())
                ->method('getResourceURIBase')
                ->will($this->returnValue('/rest/v11_2/'));
        } else {
            $requestMock->expects($this->any())
                ->method('getResourceURIBase')
                ->will($this->returnValue(self::SITE_URL . 'rest/v11_2/'));
        }

        $serviceMock = $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->setMethods(['findRoute', 'getSiteUrl'])
            ->getMock();

        $serviceMock->expects($this->any())
            ->method('findRoute')
            ->will($this->returnValue($hasRoute));

        $serviceMock->expects($this->any())
            ->method('getSiteUrl')
            ->will($this->returnValue(self::SITE_URL));

        TestReflection::setProtectedValue($serviceMock, 'request', $requestMock);

        $result = $serviceMock->getResourceURI($resource, $options);
        $this->assertSame($expected, $result);
    }

    public function providerGetResourceURI()
    {
        return [
            // resource is a string and has route
            [
                'modules/Accounts',
                true,
                ['relative' => true],
                'a.com',
                'b.com',
                'rest/v11_2/modules/Accounts',
            ],
            // resource is array, has route
            [
                ['Notes', 'id-123', 'file', 'filename'],
                true,
                ['relative' => true],
                'a.com',
                'b.com',
                'rest/v11_2/Notes/id-123/file/filename',
            ],
            // no route
            [
                'modules/abcd/efg',
                false,
                ['relative' => true],
                'a.com',
                'b.com',
                '',
            ],
            // resource is a string and has route, relative = false
            [
                'modules/Accounts',
                true,
                ['relative' => false],
                'a.com',
                'b.com',
                self::SITE_URL . 'rest/v11_2/modules/Accounts',
            ],
            // resource is array, has route, , relative = false
            [
                ['modules', 'Accounts'],
                true,
                ['relative' => false],
                'a.com',
                'b.com',
                self::SITE_URL . 'rest/v11_2/modules/Accounts',
            ],
            // resource is array, has route, relative = false
            [
                ['Notes', 'id-123', 'file', 'filename'],
                true,
                ['relative' => false],
                'a.com',
                'b.com',
                self::SITE_URL . 'rest/v11_2/Notes/id-123/file/filename',
            ],
            // no route, relative is false
            [
                'modules/abcd/efg',
                false,
                ['relative' => false],
                'a.com',
                'b.com',
                '',
            ],
            // resource is array, has route, relative = false, URL and SCRIPT_NAME
            [
                ['Notes', 'id-123', 'file', 'filename'],
                true,
                ['relative' => false],
                'a.com',
                'a.com',
                self::SITE_URL . 'rest/v11_2/Notes/id-123/file/filename',
            ],
            // resource is array, has route, URL and SCRIPT_NAME is the same
            [
                ['Notes', 'id-123', 'file', 'filename'],
                true,
                ['relative' => true],
                'a.com',
                'a.com',
                'api/rest.php/v11_2/Notes/id-123/file/filename',
            ],
            // no route, URL and SCRIPT_NAME
            [
                'modules/abcd/efg',
                false,
                ['relative' => false],
                'a.com',
                'a.com',
                '',
            ],
        ];
    }

    /**
     * @covers ::checkVersionSupport
     *
     * @dataProvider providerCheckVersionSupport
     */
    public function testCheckVersionSupport($version, $minVersion, $maxVersion, $expected)
    {
        $ref = new \ReflectionClass('RestService');
        $service = $ref->newInstanceWithoutConstructor();

        $result = TestReflection::callProtectedMethod($service, 'checkVersionSupport', [$version, $minVersion, $maxVersion,]);

        $this->assertSame($expected, $result);
    }

    public function providerCheckVersionSupport()
    {
        return [
            'version same as min and max is supported' => [
                '10',
                '10',
                '10',
                true,
            ],
            'major.minor version same as max is supported' => [
                '12.2',
                '10',
                '12.2',
                true,
            ],
            'major.minor version between min and max is supported' => [
                '13.1',
                '13',
                '13.3',
                true,
            ],
            'version greater than min and max is not supported' => [
                '11',
                '10',
                '10',
                false,
            ],
            'major.minor version less than min is not supported' => [
                '12.2',
                '13',
                '13.5',
                false,
            ],
            'major.minor version greater than max is not supported' => [
                '12.2',
                '10',
                '11',
                false,
            ],
            'minor version greater than max.minor version is not supported' => [
                '10.15',
                '10.1',
                '10.2',
                false,
            ],
        ];
    }
}
