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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Exception\HandlerCollectionException;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection;
use Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures\AnalysisHandler;
use Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures\BaseHandler;
use Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures\MappingHandler;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection
 */
class HandlerCollectionTest extends TestCase
{
    /**
     * @covers ::addHandler
     * @covers ::hasHandler
     * @covers ::removeHandler
     * @covers ::getHandler
     * @covers ::getIterator
     * @covers ::__construct
     */
    public function testCollection()
    {
        $provider = new GlobalSearch();

        $handler1 = new BaseHandler();
        $handler2 = new AnalysisHandler();
        $handler3 = new MappingHandler();

        $collection = new HandlerCollection($provider);
        $collection->addHandler($handler1);
        $collection->addHandler($handler2);
        $collection->addHandler($handler3);
        $collection->removeHandler('AnalysisHandler');

        $this->assertSame($provider, $handler1->provider);
        $this->assertSame($provider, $handler3->provider);

        $iterator = $collection->getIterator();
        $this->assertInstanceOf('ArrayIterator', $iterator);

        $this->assertTrue($collection->hasHandler('BaseHandler'));
        $this->assertTrue($collection->hasHandler('MappingHandler'));
        $this->assertFalse($collection->hasHandler('AnalysisHandler'));
        $this->assertFalse($collection->hasHandler('FooBar'));

        $this->assertSame($handler1, $collection->getHandler('BaseHandler'));
        $this->assertSame($handler3, $collection->getHandler('MappingHandler'));
    }

    /**
     * @covers ::removeHandler
     */
    public function testRemoveHandlerException()
    {
        $this->expectException(HandlerCollectionException::class);
        $this->expectExceptionMessage('Cannot remove non-existing handler FooBar');

        $collection = new HandlerCollection(new GlobalSearch());
        $collection->removeHandler('FooBar');
    }

    /**
     * @covers ::getHandler
     */
    public function testGetHandlerException()
    {
        $this->expectException(HandlerCollectionException::class);
        $this->expectExceptionMessage('Handler FooBar does not exist');

        $collection = new HandlerCollection(new GlobalSearch());
        $collection->getHandler('FooBar');
    }
}
