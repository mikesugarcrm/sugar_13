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
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Email as Field;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Email
 */
class EmailTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct()
     * @covers ::jsonSerialize()
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $field = new Field('the-id');

        $this->assertSame([
            'field_name' => 'email',
            'id' => 'the-id',
        ], $field->jsonSerialize());
    }
}
