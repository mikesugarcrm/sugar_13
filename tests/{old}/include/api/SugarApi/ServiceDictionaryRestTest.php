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

/**
 * @coversDefaultClass ServiceDictionaryRest
 */
class ServiceDictionaryRestTest extends TestCase
{
    private $sd = null;

    private $fakeEndpoints = [
        [
            'reqType' => 'GET',
            'path' => ['one', 'two', 'three'],
            'pathVars' => ['', '', ''],
            'method' => 'unittest1',
            'shortHelp' => 'short help',
            'longHelp' => 'some/path.html',
            'minVersion' => '4.9',
            'maxVersion' => '5.1',
        ],
        [
            'reqType' => 'GET',
            'path' => ['one', 'two'],
            'pathVars' => ['', ''],
            'method' => 'unittest2',
            'shortHelp' => 'short help',
            'longHelp' => 'some/path.html',
        ],
        [
            'reqType' => 'GET',
            'path' => ['one', 'two', 'three'],
            'pathVars' => ['', '', ''],
            'method' => 'unittest3',
            'shortHelp' => 'short help',
            'longHelp' => 'some/path.html',
            'extraScore' => 25.5,
            'minVersion' => '4.9',
            'maxVersion' => '5.1',
        ],
        [
            'reqType' => 'GET',
            'path' => ['<module>', '?', 'three'],
            'pathVars' => ['', '', ''],
            'method' => 'unittest4',
            'shortHelp' => 'short help',
            'longHelp' => 'some/path.html',
        ],
    ];

    protected function setUp(): void
    {
        // Create a new mock of the ServiceDictionaryRest and empty its endpoints array
        $this->sd = new ServiceDictionaryRestMainMock();
        $this->sd->preRegisterEndpoints();
    }

    /**
     * @covers ::registerEndpoints
     */
    public function testRegisterEndpointsNoEndpoints()
    {
        $blank = $this->sd->getRegisteredEndpoints();
        $this->assertEquals(0, safeCount($blank));
    }

    /**
     * @covers ::registerEndpoints
     */
    public function testRegisterEndpointsOneEndpoint()
    {
        $this->sd->registerEndpoints([$this->fakeEndpoints[0]], 'fake/unittest1.php', 'unittest1', 'base', 0);
        $oneTest = $this->sd->getRegisteredEndpoints();
        $this->assertTrue(isset($oneTest['3']['base']['GET']['one']['two']['three'][0]['method']));
    }

    /**
     * @covers ::registerEndpoints
     */
    public function testRegisterEndpointsAllEndpoints()
    {
        $this->sd->registerEndpoints($this->fakeEndpoints, 'fake/unittest1.php', 'unittest1', 'base', 0);
        $allTest = $this->sd->getRegisteredEndpoints();
        $this->assertTrue(isset($allTest['3']['base']['GET']['one']['two']['three'][0]['method']));
        $this->assertTrue(isset($allTest['2']['base']['GET']['one']['two'][0]['method']));
    }

    /**
     * @covers ::lookupRoute
     */
    public function testLookupRoute()
    {
        $this->sd->registerEndpoints($this->fakeEndpoints, 'fake/unittest1.php', 'unittest1', 'base', 0);
        $portalEndpoint = $this->fakeEndpoints[3];
        $portalEndpoint['method'] = 'portaltest4';
        $this->sd->registerEndpoints([$portalEndpoint], 'portal/unittest1.php', 'portaltest4', 'portal', 0);

        $portalEndpoint = $this->fakeEndpoints[2];
        $portalEndpoint['method'] = 'portaltest3';
        $portalEndpoint['path'][2] = 'portal';
        $this->sd->registerEndpoints([$portalEndpoint], 'portal/unittest1.php', 'portaltest3', 'portal', 0);

        $allTest = $this->sd->getRegisteredEndpoints();
        $this->sd->pullDictFromBuffer();

        $this->assertTrue(isset($allTest['3']['base']['GET']['one']['two']['three'][0]['method']));
        $this->assertTrue(isset($allTest['2']['base']['GET']['one']['two'][0]['method']));
        $this->assertEquals('portaltest4', $allTest['3']['portal']['GET']['<module>']['?']['three'][0]['method']);

        // Make sure we can find a normal route
        $route = $this->sd->lookupRoute(['one', 'two', 'three'], 5.0, 'GET', 'base');
        $this->assertEquals('unittest3', $route['method']);

        // Make sure we find a base route if there isn't a platform specific route
        $route = $this->sd->lookupRoute(['one', 'two', 'three'], 5.0, 'GET', 'portal');
        $this->assertEquals('unittest3', $route['method']);

        // Make sure we find a platform specific route
        $route = $this->sd->lookupRoute(['one', 'two', 'portal'], 5.0, 'GET', 'portal');
        $this->assertEquals('portaltest3', $route['method']);

        // Make sure we correctly compare the version string
        $route = $this->sd->lookupRoute(['one', 'two', 'three'], '4.10', 'GET', 'base');
        $this->assertEquals('unittest3', $route['method']);
    }

    /**
     * Checks that if the API version is lower than a route's minVersion, or
     * higher than a route's maxVersion, the route is not found in lookupRoute
     *
     * @covers ::lookupRoute
     * @dataProvider providerLookupRouteBadApiVersion
     * @param $version
     * @throws SugarApiExceptionNoMethod
     */
    public function testLookupRouteBadApiVersion($version)
    {
        $this->sd->registerEndpoints($this->fakeEndpoints, 'fake/unittest1.php', 'unittest1', 'base', 0);
        $this->sd->pullDictFromBuffer();
        $this->expectException(SugarApiExceptionNoMethod::class);
        $this->sd->lookupRoute(['one', 'two', 'three'], $version, 'GET', 'base');
    }

    public function providerLookupRouteBadApiVersion()
    {
        return [
            ['4.8'],
            ['5.2'],
        ];
    }
}

class ServiceDictionaryRestMainMock extends ServiceDictionaryRest
{
    public function pullDictFromBuffer()
    {
        $this->dict = $this->endpointBuffer;
    }
}
