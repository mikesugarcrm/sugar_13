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
 *  RS-162: Prepare SugarList Api.
 */
class RS162Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testParseArgs()
    {
        $api = $this->getMockForAbstractClass('SugarListApi');
        $rest = SugarTestRestUtilities::getRestServiceMock();
        $result = $api->parseArguments(
            $rest,
            []
        );
        $this->assertArrayHasKey('limit', $result);
        $this->assertArrayHasKey('offset', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('orderBy', $result);
    }

    public function testConvertOrderByToSql()
    {
        $api = $this->getMockForAbstractClass('SugarListApi');
        $result = $api->convertOrderByToSql(['date' => 'ASC', 'name' => 'DESC']);
        $this->assertEquals('date ASC,name DESC', $result);
    }
}
