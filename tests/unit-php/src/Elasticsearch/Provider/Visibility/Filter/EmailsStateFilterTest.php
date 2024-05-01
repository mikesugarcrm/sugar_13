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
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Filter\EmailsStateFilter;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Filter\EmailsStateFilter
 */
class EmailsStateFilterTest extends TestCase
{
    public function buildFilterProvider()
    {
        return [
            ['Draft'],
            ['Archived'],
        ];
    }

    /**
     * @covers ::buildFilter
     * @dataProvider buildFilterProvider
     */
    public function testBuildFilter($state)
    {
        $filter = new EmailsStateFilter();
        $term = $filter->buildFilter(['state' => $state]);
        $param = $term->getParam('Emails__state.emails_state');

        $this->assertSame($state, $param['value']);
        $this->assertArrayHasKey('boost', $param);
    }
}
