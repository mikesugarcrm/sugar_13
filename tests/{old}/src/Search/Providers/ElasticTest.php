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

namespace Sugarcrm\SugarcrmTests\Search\Providers;

use Sugarcrm\Sugarcrm\Search\Providers\Elastic;
use PHPUnit\Framework\TestCase;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Search\Providers\Elastic
 */
class ElasticTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::formatData
     */
    public function testFormatData()
    {
        $module = 'KBContents';
        $data = [
            'query_time' => 123,
            'total' => 1,
            'next_offset' => -1,
            'records' => [
                [
                    'id' => 'testid',
                    'name' => 'testname',
                    'kbdocument_body' => 'test doc body',
                    'commentlog' => 'log',
                    '_acl' => 'acl',
                    '_score' => 1.0,
                    '_module' => $module,
                    '_erased_fields' => 'whatever',
                ],
            ],
        ];
        $provider = new Elastic();
        $newData = $provider->formatData($data);

        // these attributes should have been unset
        $propertiesToCopy = $provider->getPropertiesToCopy();
        foreach ($propertiesToCopy as $proerty) {
            $this->assertArrayHasKey($proerty, $newData);
        }

        // these fields should have been added
        $settings = \VardefManager::getModuleProperty('KBContent', 'generic_search');
        $this->assertNotNull($settings);
        $mapping = $settings['Elastic']['mapping'];
        if (!empty($mapping) && is_array($mapping)) {
            foreach ($mapping as $new => $original) {
                if (isset($data['records'][0])) {
                    $this->assertArrayHasKey($new, $newData['records'][0]);
                }
            }
        }

        // url should have been added
        $this->assertArrayHasKey('url', $newData['records'][0]);
    }
}
