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

use PHPUnit\Framework\TestCase;

/**
 * @ticket 59069
 */
class Bug59069Test extends TestCase
{
    /**
     * @param string $type
     *
     * @dataProvider provider
     */
    public function testNoExceptionThrown($type)
    {
        $context = new stdClass();
        $context->field_defs = [
            'test' => [
                'type' => $type,
            ],
        ];

        $expr = new SugarFieldExpression('test');
        $expr->context = $context;

        $context->test = '';
        $this->assertFalse($expr->evaluate());

        $context->test = 'foobar';
        $this->assertFalse($expr->evaluate());
    }

    public static function provider()
    {
        return [
            ['datetime'],
            ['datetimecombo'],
            ['date'],
            ['time'],
        ];
    }
}
