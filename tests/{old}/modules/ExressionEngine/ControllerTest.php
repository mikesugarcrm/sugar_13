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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers ExpressionEngineController
 */
class ExpressionEngine_ControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        $_REQUEST = [];
    }

    /**
     * @param string $module
     * @param array $fields
     * @param string $id
     *
     * @dataProvider badRequestProvider
     */
    public function testBadRequest($module, $fields, $id = null)
    {
        $_REQUEST = [
            'tmodule' => $module,
            'fields' => $fields,
            'record_id' => $id,
        ];


        /** @var ExpressionEngineController|MockObject $controller */
        $controller = $this->createPartialMock('ExpressionEngineController', ['display']);

        // assert that display method was invoked which means no PHP error was triggered
        $controller->expects($this->once())->method('display');
        $controller->action_getRelatedValues();
    }

    public static function badRequestProvider()
    {
        return [
            'non-json-string' => ['Accounts', 'non-json-string'],
            'bad-common-field-defs' => ['Accounts', json_encode([[]])],
            'bad-relate-field-defs' => ['Accounts', json_encode([
                [
                    'link' => 'foo',
                    'type' => 'related',
                ],
            ]),
            ],
            'bad-rollup-field-defs' => ['Accounts', json_encode([
                [
                    'link' => 'foo',
                    'type' => 'rollupSum',
                ],
            ]),
            ],
            'bean-not-found' => ['Accounts', json_encode([
                [
                    'link' => 'contacts',
                    'type' => 'count',
                ],
            ]), 'non-existing-id',
            ],
        ];
    }
}
