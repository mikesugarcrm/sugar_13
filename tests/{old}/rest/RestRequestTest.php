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

class RestRequestTest extends TestCase
{
    /**
     * @dataProvider versionProvider
     */
    public function testVersion($req, $header, $expUrlVersion, $expVersion)
    {
        $service = array_merge(['REQUEST_METHOD' => 'GET'], $header);
        $restRequest = new RestRequest($service, ['__sugar_url' => $req]);
        $version = $restRequest->getVersion();
        $urlVersion = $restRequest->getUrlVersion();
        $this->assertSame($expUrlVersion, $urlVersion);
        $this->assertSame($expVersion, $version);
    }

    public function versionProvider()
    {
        $headerName = 'HTTP_ACCEPT';
        return [
            // no header, only URL
            ['v10/Accounts/by_country', [], 'v10', '10'],
            // no header, only URL with minor version
            ['v12_1/Accounts/by_country', [], 'v12_1', '12.1'],
            // double path
            ['//v99/Accounts/by_country/', [], 'v99', '99'],
            // Accept header has version
            [
                '/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core+xml; version=11'],
                'v11',
                '11',
            ],
            // Accept header has version with minor version
            [
                '/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core+xml; version=11.2'],
                'v11_2',
                '11.2',
            ],
            // only Accept header has version, with no response type
            [
                '/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core; version=10'],
                'v10',
                '10',
            ],
            // only Accept header has MAJOR.MINOR version, with no response type
            [
                '/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core; version=10.2'],
                'v10_2',
                '10.2',
            ],
            // Header with qualifier indicator
            [
                '/Accounts/by_country',
                [$headerName =>
                    'application/vnd.sugarcrm.core+xml; version=11;
                    q=0.5, application/vnd.sugarcrm.core+json; version=11',
                ],
                'v11',
                '11',
            ],
            // url version not with _, will not be detected and header version will be used
            [
                'v10.1/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core+xml; version=11.2'],
                'v11_2',
                '11.2',
            ],
        ];
    }

    /**
     * @dataProvider versionExceptionProvider
     */
    public function testVersionException($req, $header)
    {
        $service = array_merge(['REQUEST_METHOD' => 'GET'], $header);
        $restRequest = new RestRequest($service, ['__sugar_url' => $req]);

        $this->expectException(SugarApiExceptionIncorrectVersion::class);
        $restRequest->getVersion();
    }

    public function versionExceptionProvider()
    {
        $headerName = 'HTTP_ACCEPT';
        return [
            'both header and URL have version' => [
                'v10/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core+xml; version=11'],
            ],
            'neither Header nor Url has versoin' => ['/Accounts/by_country/', []],
            'not _ in url version' => ['v42.3/Accounts/by_country?foo=bar', []],
            'not 2-digit url version' => ['//v7/Accounts/by_country/', []],
            '3-digit, and no URL version' => [
                '/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core+xml; version=101'],
            ],
            'header version triple digit minor version, and no URL version' => [
                '/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core+xml; version=10.123'],
            ],
            'header version has random string, and no URL version' => [
                '/Accounts/by_country',
                [$headerName => 'application/vnd.sugarcrm.core+xml; version=v10.1x'],
            ],
        ];
    }

    public function testMethod()
    {
        $serv = ['REQUEST_METHOD' => 'GET'];
        $r = new RestRequest($serv, []);
        $this->assertEquals('GET', $r->getMethod());

        $serv = ['REQUEST_METHOD' => 'POST'];
        $r = new RestRequest($serv, []);
        $this->assertEquals('POST', $r->getMethod());
    }

    /**
     * @dataProvider pathProvider
     * @param string $path
     * @param array $parsedpath
     */
    public function testParsePath($path, $parsedpath)
    {
        $r = new RestRequest(['REQUEST_METHOD' => 'GET'], ['__sugar_url' => $path]);
        $this->assertEquals($r->path, $parsedpath);
    }

    public function pathProvider()
    {
        return [
            ['v10/metadata/public', ['metadata', 'public']],
            ['//v10/metadata/public//', ['metadata', 'public']],
            ['v42/metadata/123/', ['metadata', '123']],
            ['blah/metadata/123/', ['blah', 'metadata', '123']],
            ['/v12/metadata/../public/', ['metadata', '..', 'public']],
        ];
    }

    /**
     * @dataProvider pathVarsProvider
     */
    public function testGetPathVars($path, $route, $vars)
    {
        $r = new RestRequest(['REQUEST_METHOD' => 'GET'], ['__sugar_url' => $path]);
        $this->assertEquals($r->getPathVars($route), $vars);
    }

    public function pathVarsProvider()
    {
        return [
            ['v10/metadata/public', [], []],
            ['v10/metadata/public', ['pathVars' => ['foo']], ['foo' => 'metadata']],
            ['v10/metadata/public', ['pathVars' => ['', 'foo']], ['foo' => 'public']],
            ['v10/metadata/public', ['pathVars' => ['', '', 'foo']], []],
        ];
    }

    /**
     * @dataProvider headersProvider
     */
    public function testGetRequestHeaders($serv, $header, $value)
    {
        $serv['REQUEST_METHOD'] = 'GET';
        $r = new RestRequest($serv, ['__sugar_url' => 'v10/metadata/public']);
        if (empty($value)) {
            $this->assertArrayNotHasKey($header, $r->getRequestHeaders());
        } else {
            $this->assertEquals($value, $r->getHeader($header));
        }
    }

    public function headersProvider()
    {
        return [
            [['HTTP_HOST' => 'foo'], 'HOST', 'foo'],
            [['HTTP_PORT' => '123'], 'HOST', null],
            [['HTTP_PORT_NUMBER' => '123'], 'PORT_NUMBER', '123'],
        ];
    }


    public function testGetResourceURIBase()
    {
        $r = new RestRequest([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '__sugar_url=v10/metadata/public&type_filter=&module_filter=&platform=base&_hash=688d8896f98ff0d0db7fca1aad465809',
            'REQUEST_URI' => '/sugar7/rest/v10/metadata/public?type_filter=&module_filter=&platform=base&_hash=688d8896f98ff0d0db7fca1aad465809',
            'SCRIPT_NAME' => '/sugar7/api/rest.php',
        ], ['__sugar_url' => 'v10/metadata/public']);

        $this->assertEquals($GLOBALS['sugar_config']['site_url'] . '/rest/v10/', $r->getResourceURIBase('v10'));
    }

    /**
     * @dataProvider rawPathProvider
     * @param array $req
     * @param string $path
     */
    public function testGetRawPath($req, $path)
    {
        $serv = ['REQUEST_METHOD' => 'GET'];
        $r = new RestRequest($serv, $req);

        $this->assertEquals($path, $r->getRawPath());

        if (!empty($req['__sugar_url'])) {
            $serv['PATH_INFO'] = $req['__sugar_url'];
            unset($req['__sugar_url']);
            $r = new RestRequest($serv, $req);

            $this->assertEquals($path, $r->getRawPath());
        }
    }

    public function rawPathProvider()
    {
        return [
            [[], '/'],
            [['' => '/foo'], '/'],
            [['__sugar_url' => '/foo'], '/foo'],
            [['PATH_INFO' => '/foo'], '/'],
            [['__sugar_url' => '//foo//../bar'], '//foo//../bar'],
            [null, '/'],
        ];
    }
}
