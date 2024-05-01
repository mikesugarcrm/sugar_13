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

class WebAccessTest extends TestCase
{
    /**
     * @test
     * @dataProvider allowedUrlProvider
     */
    public function accessIsAllowed($url)
    {
        $this->assertNotEquals(403, $this->fetch($url));
    }

    public static function allowedUrlProvider()
    {
        return [
            'ytree' => [
                '/vendor/ytree/treeutil.js',
            ],
            'custom-vendor' => [
                '/custom/vendor/autoload.php',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider forbiddenUrlProvider
     */
    public function accessIsForbidden($url)
    {
        $this->assertEquals(404, $this->fetch($url));
    }

    public static function forbiddenUrlProvider()
    {
        return [
            'log' => [
                '/install.log',
            ],
            'composer' => [
                '/composer.json',
            ],
            'modules' => [
                '/modules/Accounts/Account.php',
            ],
            'double-slash' => [
                '/cache//class_map.php',
            ],
            'case-insensitive' => [
                '/Vendor/AutoLoad.php',
            ],
        ];
    }

    /**
     * @param string $url The URL relative to the base instance URL
     * @return string HTTP response status code
     */
    private function fetch($url)
    {
        global $sugar_config;

        $url = rtrim($sugar_config['site_url'], '/') . $url;

        fclose(
            fopen($url, 'r', false, stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                ],
            ]))
        );

        return explode(' ', array_shift($http_response_header))[1];
    }
}
