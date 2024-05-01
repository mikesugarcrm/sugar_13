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

class RestServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > 1) {
            ob_end_flush();
        }
    }

    public function testGetRequestArgs()
    {
        $request = $this->createPartialMock('RestRequest', ['getPathVars', 'getPostContents', 'getQueryVars']);
        $request->expects($this->any())
            ->method('getPathVars')
            ->will($this->returnValue([]));

        $_GET = ['my_json' => '{"christopher":"walken","bill":"murray"}'];
        $request->expects($this->any())
            ->method('getPostContents')
            ->will($this->onConsecutiveCalls(
                '',
                '{"my_json":{"christopher":"walken","bill":"murray"}}',
                '{"my_json":{"christopher":"walken","bill":"murray"}}}'
            ));

        $request->expects($this->any())
            ->method('getQueryVars')
            ->will($this->onConsecutiveCalls(
                ['my_json' => '{"christopher":"walken","bill":"murray"}'],
                ['my_json' => '{"christopher":"walken","bill":"murray"}}'],
                []
            ));


        $service = new RestService();
        SugarTestReflection::setProtectedValue($service, 'request', $request);

        $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', [['jsonParams' => ['my_json']]]);
        $this->assertArrayHasKey('christopher', $output['my_json'], 'Missing Christopher => Walken #1');
        $this->assertArrayHasKey('bill', $output['my_json'], 'Missing Bill => Murray #1');

        $hadException = false;
        try {
            $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', [['jsonParams' => ['my_json']]]);
        } catch (SugarApiExceptionInvalidParameter $e) {
            $hadException = true;
        }

        $this->assertTrue($hadException, 'Did not throw an exception on invalid JSON #1');

        $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', [[]]);

        $this->assertArrayHasKey('christopher', $output['my_json'], 'Missing Christopher => Walken #2');
        $this->assertArrayHasKey('bill', $output['my_json'], 'Missing Bill => Murray #2');

        $hadException = false;
        try {
            $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', [[]]);
        } catch (SugarApiExceptionInvalidParameter $e) {
            $hadException = true;
        }

        $this->assertTrue($hadException, 'Did not throw an exception on invalid JSON #2');
    }
}
