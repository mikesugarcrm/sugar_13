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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\Visibility\Filter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Filter\KBIsExternalFilter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Filter\KBIsExternalFilter
 */
class KBIsExternalFilterTest extends TestCase
{
    public function buildFilterProvider()
    {
        return [
            [1],
            [0],
        ];
    }

    /**
     * @covers ::buildFilter
     * @dataProvider buildFilterProvider
     */
    public function testBuildFilter($expected)
    {
        $filter = new KBIsExternalFilter();
        $term = $filter->buildFilter(['module' => 'KBContents', 'expected' => $expected]);
        $param = $term->getParam('KBContents__is_external.kbvis');

        $this->assertArrayHasKey('value', $param);
        $this->assertSame($expected, $param['value']);
    }
}
