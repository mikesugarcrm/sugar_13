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

namespace Sugarcrm\SugarcrmTestsUnit\DataPrivacy\Erasure\Field;

use PHPUnit\Framework\TestCase;
use SugarBean;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Scalar as Field;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Scalar
 */
class ScalarTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct()
     * @covers ::jsonSerialize()
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $field = new Field('name');

        $this->assertSame('name', $field->jsonSerialize());
    }

    /**
     * @test
     * @covers ::__construct()
     * @covers ::erase()
     */
    public function erase()
    {
        $bean = $this->createMock(SugarBean::class);
        $bean->foo = 'x';
        $bean->bar = 'y';

        $field = new Field('foo');
        $field->erase($bean);

        $this->assertNull($bean->foo);
        $this->assertSame('y', $bean->bar);
    }
}
