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
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Filter\KBExpDateFilter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Filter\KBExpDateFilter
 */
class KBExpDateFilterTest extends TestCase
{
    /**
     * @covers ::buildFilter
     */
    public function testBuildFilter()
    {
        $filter = new KBExpDateFilter();
        $term = $filter->buildFilter(['module' => 'KBContents', 'range' => ['gte' => 'now/d']]);

        $expected = [
            'bool' => [
                'should' => [
                    [
                        'range' => [
                            'KBContents__exp_date.kbvis' => [
                                'gte' => 'now/d',
                            ],
                        ],
                    ],
                    [
                        'bool' => [
                            'must_not' => [
                                [
                                    'exists' => [
                                        'field' => 'KBContents__exp_date.kbvis',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $term->toArray());
    }
}
