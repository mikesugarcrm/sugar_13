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

class MetadataApiTest extends TestCase
{
    /**
     * @var MetadataApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->api = new MetadataApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testGetModuleTabMap()
    {
        // This used to live in the MetadataApi, hence the reason for this test
        $mm = MetaDataManager::getManager();
        $data = $mm->getModuleTabMap();

        // Test see that the map is not empty and an array
        $this->assertIsArray($data, 'module_tab_map is not an array');
        $this->assertNotEmpty($data, 'Module Tab Map is empty');

        // Test that a known value is in the data
        $this->assertEquals('Emails', $data['EmailTemplates'], 'EmailTemplates not translated properly');
    }

    /**
     * Test asserts behavior of getAllMetadata
     */
    public function testGetAllMetadata()
    {
        $result = $this->api->getAllMetadata($this->serviceMock, []);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);

        $this->assertArrayHasKey('full_module_list', $result);
        $this->assertArrayHasKey('modules', $result);
        $this->assertArrayHasKey('hidden_subpanels', $result);
        $this->assertArrayHasKey('currencies', $result);
        $this->assertArrayHasKey('module_tab_map', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('views', $result);
        $this->assertArrayHasKey('layouts', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('jssource', $result);
        $this->assertArrayHasKey('server_info', $result);
        $this->assertArrayHasKey('logo_url', $result);
        $this->assertArrayHasKey('languages', $result);
        $this->assertArrayHasKey('_override_values', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertIsArray($result['full_module_list']);
        $this->assertIsArray($result['modules']);
        $this->assertIsArray($result['hidden_subpanels']);
        $this->assertIsArray($result['currencies']);
        $this->assertIsArray($result['module_tab_map']);
        $this->assertIsArray($result['fields']);
        $this->assertIsArray($result['views']);
        $this->assertIsArray($result['layouts']);
        $this->assertIsArray($result['labels']);
        $this->assertIsArray($result['config']);
        $this->assertIsArray($result['relationships']);
        $this->assertIsString($result['jssource']);
        $this->assertIsArray($result['server_info']);
        $this->assertIsString($result['logo_url']);
        $this->assertIsArray($result['languages']);
        $this->assertIsArray($result['_override_values']);
        $this->assertIsString($result['_hash']);
    }

    /**
     * Test asserts behavior of getPublicMetadata
     */
    public function testGetPublicMetadata()
    {
        $result = $this->api->getPublicMetadata($this->serviceMock, []);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);

        $this->assertArrayHasKey('modules', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('views', $result);
        $this->assertArrayHasKey('layouts', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('jssource', $result);
        $this->assertArrayHasKey('logo_url', $result);
        $this->assertArrayHasKey('_override_values', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertIsArray($result['modules']);
        $this->assertIsArray($result['fields']);
        $this->assertIsArray($result['views']);
        $this->assertIsArray($result['layouts']);
        $this->assertIsArray($result['labels']);
        $this->assertIsArray($result['config']);
        $this->assertIsString($result['jssource']);
        $this->assertIsString($result['logo_url']);
        $this->assertIsArray($result['_override_values']);
        $this->assertIsString($result['_hash']);
    }

    /**
     * Test asserts behavior of getLanguage
     */
    public function testGetLanguage()
    {
        $result = $this->api->getLanguage($this->serviceMock, ['lang' => 'en_us']);

        $this->assertNotEmpty($result);
        $this->assertJson($result);
        $result = json_decode($result, true);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('app_list_strings', $result);
        $this->assertArrayHasKey('app_strings', $result);
        $this->assertArrayHasKey('mod_strings', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertIsArray($result['app_list_strings']);
        $this->assertIsArray($result['app_strings']);
        $this->assertIsArray($result['mod_strings']);
        $this->assertIsString($result['_hash']);
    }

    /**
     * Test asserts behavior of getPublicLanguage
     */
    public function testGetPublicLanguage()
    {
        $result = $this->api->getPublicLanguage($this->serviceMock, ['lang' => 'en_us']);

        $this->assertNotEmpty($result);
        $this->assertJson($result);
        $result = json_decode($result, true);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('app_list_strings', $result);
        $this->assertArrayHasKey('app_strings', $result);
        $this->assertArrayHasKey('_hash', $result);

        $this->assertIsArray($result['app_list_strings']);
        $this->assertIsArray($result['app_strings']);
        $this->assertIsString($result['_hash']);
    }
}
