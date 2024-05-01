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

class BulkApiTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > 1) {
            ob_end_flush();
        }
    }

    public function testBulkApi()
    {
        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $requests = [
            ['url' => '/v10/me', 'method' => 'GET'],
            ['url' => '/v10/lang/en_us', 'method' => 'GET'],
            ['url' => '/v10/404', 'method' => 'GET'], // no such route
            ['url' => '/v10/Accounts/x123-456x', 'method' => 'PUT'], // no such record
            ['url' => "/v10/Users/{$GLOBALS['current_user']->id}?test=1"], // implied GET
            ['url' => "/v10/Users/{$GLOBALS['current_user']->id}/link"], // missing param
            ['url' => '/v10/Users', 'method' => 'POST', 'data' => 'b;ah'], // bad JSON
            ['url' => '/v10/Users', 'method' => 'POST', 'data' => json_encode(['id' => $GLOBALS['current_user']->id, 'name' => 'test'])], // unauthorized
            // queries
            ['url' => "/v10/Users?fields=name,date_modified,id&filter=[{\"id\":\"{$GLOBALS['current_user']->id}\"}]", 'method' => 'GET'],
            ['url' => '/v07/me'], // bad version format
            ['url' => '/v11.7/me'], // bad version format
            ['url' => '/me', 'method' => 'GET'], // no version in url
            ['url' => '/lang/en_us', 'method' => 'GET'], // no version in url
            // invalid, both Header and url have version
            [
                'url' => '/v10/me',
                'headers' => ['ACCEPT' => 'application/vnd.sugarcrm.core; version=10'],
            ],
            // valid, header version but no url version
            [
                'url' => '/me',
                'headers' => ['ACCEPT' => 'application/vnd.sugarcrm.core+xml; version=10'],
            ],
        ];
        $apiClass = new BulkApi();
        $args = ['requests' => $requests];
        $result = $apiClass->bulkCall($api, $args);

        $this->assertSameSize($requests, $result);
        foreach ($result as $i => $item) {
            $this->assertArrayHasKey('contents', $result[$i], "Missing contents for response $i");
            $this->assertArrayHasKey('status', $result[$i], "Missing status for response $i");
        }

        // test plain req
        $this->assertEquals('200', $result[0]['status'], 'Bad status for request 0');
        $this->assertEquals($GLOBALS['current_user']->id, $result[0]['contents']['current_user']['id'], 'Bad data for request 0');
        $this->assertArrayHasKey('ETag', $result[0]['headers']);
        // test headers
        $this->assertEquals('200', $result[1]['status'], 'Bad status for request 1');
        $this->assertStringContainsString('app_list_strings', $result[1]['contents']);
        $this->assertArrayHasKey('Content-Type', $result[1]['headers']);
        $this->assertEquals('application/json', $result[1]['headers']['Content-Type']);
        // test 404 from unknown endpoint
        $this->assertEquals('404', $result[2]['status'], 'Bad status for request 2');
        // test 404 from unknown record
        $this->assertEquals('404', $result[3]['status'], 'Bad status for request 3');
        // implied GET
        $this->assertEquals('200', $result[4]['status'], 'Bad status for request 4');
        $this->assertEquals($GLOBALS['current_user']->id, $result[4]['contents']['id'], 'Bad data for request 4');
        // missing param
        $this->assertEquals('404', $result[5]['status'], 'Bad status for request 5');
        // bad JSON
        $this->assertEquals('422', $result[6]['status'], 'Bad status for request 6');
        // unauthorized
        $this->assertEquals('403', $result[7]['status'], 'Bad status for request 7');
        // with parameters
        $this->assertEquals('200', $result[8]['status'], 'Bad status for request 8');
        $this->assertEquals(-1, $result[8]['contents']['next_offset'], 'Bad next offset for request 8');
        $this->assertEquals($GLOBALS['current_user']->id, $result[8]['contents']['records'][0]['id'], 'Bad fetched ID for request 8');
        // bad versions
        $this->assertEquals('301', $result[9]['status'], 'Bad status for request 9');
        $this->assertEquals('301', $result[10]['status'], 'Bad status for request 10');
        $this->assertEquals('301', $result[11]['status'], 'Bad status for request 11');
        $this->assertEquals('301', $result[12]['status'], 'Bad status for request 12');
        // header versions
        $this->assertEquals('301', $result[13]['status'], 'Bad status for request 13');
        $this->assertEquals('200', $result[14]['status'], 'Bad status for request 14');
    }

    public function testBulkApiError()
    {
        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $requests = [
            ['Xurl' => '/v10/me', 'method' => 'GET'],
        ];
        $apiClass = new BulkApi();
        $args = ['requests' => $requests];

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $apiClass->bulkCall($api, $args);
    }
}
