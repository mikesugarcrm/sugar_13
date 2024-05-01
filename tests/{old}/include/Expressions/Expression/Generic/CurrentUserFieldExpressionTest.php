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
 * @coversDefaultClass CurrentUserFieldExpression
 */
class CurrentUserFieldExpressionTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->first_name = 'SugarUser';
        $current_user->field_defs = [
            'first_name' => [
                'name' => 'first_name',
                'type' => 'name',
                'calculation_visible' => true,
            ],
        ];
        $current_user->save();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::evaluate
     */
    public function testInvalidUserFieldName()
    {
        $expr = 'currentUserField("not_a_real_field")';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('currentUserField: Parameter "not_a_real_field" is not a valid User field');

        Parser::evaluate($expr)->evaluate();
    }

    /**
     * @covers ::evaluate
     */
    public function testValidUserNameField()
    {
        $expr = 'currentUserField("first_name")';

        $result = Parser::evaluate($expr)->evaluate();
        $this->assertEquals('SugarUser', $result);
    }
}
