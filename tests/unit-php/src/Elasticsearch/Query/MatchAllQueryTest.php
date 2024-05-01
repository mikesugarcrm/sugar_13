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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\MatchAllQuery
 */
class MatchAllQueryTest extends TestCase
{
    /**
     * @covers ::build
     */
    public function testBuild()
    {
        $matchAllQueryMock = TestMockHelper::getObjectMock(
            $this,
            \Sugarcrm\Sugarcrm\Elasticsearch\Query\MatchAllQuery::class
        );

        $this->assertInstanceOf(\Elastica\Query\MatchAll::class, $matchAllQueryMock->build());
    }
}
