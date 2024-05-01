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

include_once 'modules/ConsoleConfiguration/clients/base/api/ConsoleConfigDefaultMetaDataApi.php';

/**
 * @coversDefaultClass ConsoleConfigDefaultMetaDataApi
 */
class ConsoleConfigDefaultMetaDataApiTest extends TestCase
{
    /**
     * @var RestService null
     */
    protected $service = null;

    /**
     * @var ConsoleConfigDefaultMetaDataApi
     */
    protected $api;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('app_list_strings');
        $this->api = new ConsoleConfigDefaultMetaDataApi();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::getDefaultMetadata
     */
    public function testGetDefaultMetadata()
    {
        $modules = 'Accounts,Cases';
        $type = 'view';
        $name = 'multi-line-list';
        $args = ['name' => $name, 'type' => $type, 'modules' => $modules];
        $defaultFiles = [];

        foreach (explode(',', $modules) as $mod) {
            $defaultFiles[$mod] = "modules/{$mod}/clients/base/{$type}s/{$name}/{$name}.php";
        }

        $ret = $this->api->getDefaultMetadata($this->service, $args);

        // ensure the data returned from the api is the same as the data read from file directly
        foreach (explode(',', $modules) as $mod) {
            $viewdefs = [];
            require $defaultFiles[$mod];
            $this->assertSame($viewdefs[$mod]['base'][$type][$name], $ret[$mod]);
        }
    }
}
