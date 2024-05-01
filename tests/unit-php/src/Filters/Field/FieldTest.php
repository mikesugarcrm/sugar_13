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

namespace Sugarcrm\SugarcrmTestsUnit\Filters\Field;

use ServiceBase;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Field\Field;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Field\Field
 */
class FieldTest extends TestCase
{
    public function filterProvider()
    {
        return [
            'filter is a string' => [
                'test',
            ],
            'filter is an object' => [
                [
                    '$starts' => 'test',
                ],
            ],
        ];
    }

    /**
     * @covers ::apiSerialize
     * @dataProvider filterProvider
     */
    public function testApiSerialize($filter)
    {
        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $field = new Field('name', $filter);

        $actual = $field->apiSerialize($api);

        $this->assertSame($filter, $actual);
    }

    /**
     * @covers ::apiUnserialize
     * @dataProvider filterProvider
     */
    public function testApiUnserialize($filter)
    {
        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $field = new Field('name', $filter);

        $actual = $field->apiUnserialize($api);

        $this->assertSame($filter, $actual);
    }
}
