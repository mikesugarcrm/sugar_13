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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Mapping\Property;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\RawProperty;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\RawProperty
 */
class RawPropertyTest extends TestCase
{
    /**
     * @covers ::setMapping
     * @covers ::getMapping
     */
    public function testSetGetMapping()
    {
        $field = new RawProperty();

        // initial mapping
        $field->setMapping(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $field->getMapping());

        // second mapping (overwrites previous)
        $field->setMapping(['more' => 'beer']);
        $this->assertSame([
            'more' => 'beer',
        ], $field->getMapping());
    }

    /**
     * @covers ::addCopyTo
     */
    public function testAddCopyTo()
    {
        $field = new RawProperty();

        // add first copy to field
        $field->addCopyTo('foo');
        $this->assertSame([
            'copy_to' => ['foo'],
        ], $field->getMapping());

        // add second copy to field
        $field->addCopyTo('bar');
        $this->assertSame([
            'copy_to' => [
                'foo',
                'bar',
            ],
        ], $field->getMapping());

        // add existing field again, should not change
        $field->addCopyTo('foo');
        $this->assertSame([
            'copy_to' => [
                'foo',
                'bar',
            ],
        ], $field->getMapping());
    }
}
