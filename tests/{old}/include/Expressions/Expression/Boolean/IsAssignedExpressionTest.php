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

class IsAssignedExpressionTest extends TestCase
{
    /**
     * @var aCase
     */
    protected $case = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->case = SugarTestCaseUtilities::createCase();
    }

    protected function tearDown(): void
    {
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::evaluate
     */
    public function testIsOwner()
    {
        $expr = 'isAssigned()';
        $this->case->assigned_user_id = $GLOBALS['current_user']->id;
        $result = Parser::evaluate($expr, $this->case)->evaluate();
        $this->assertSame('true', strtolower($result));
        $this->case->assigned_user_id = '';
        $result = Parser::evaluate($expr, $this->case)->evaluate();
        $this->assertSame('false', strtolower($result));
    }
}
