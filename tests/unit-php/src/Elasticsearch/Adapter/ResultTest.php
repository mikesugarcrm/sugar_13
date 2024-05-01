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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Adapter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Result;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Result
 */
class ResultTest extends TestCase
{
    /**
     * @covers ::__call
     * @return void
     */
    public function testCallable()
    {
        $hit = ['foo' => 'bar'];
        $adapter = new Result(new \Elastica\Result($hit));
        $this->assertEquals($hit, $adapter->getHit());
    }

    /**
     * @covers ::__call
     * @return void
     */
    public function testNotCallable()
    {
        $hit = ['foo' => 'bar'];
        $adapter = new Result(new \Elastica\Result($hit));
        $this->assertNull($adapter->callUnknownMethod());
    }
}
