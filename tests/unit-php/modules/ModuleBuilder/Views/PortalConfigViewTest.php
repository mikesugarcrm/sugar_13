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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ModuleBuilder\Views;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \ViewPortalConfig
 */
class PortalConfigViewTest extends TestCase
{
    /**
     * @covers ::sanitizeUserList
     * @dataProvider providerSanitizeUserList
     * @param array $userList a list of User ID => Full Name
     * @param array $expectedResult a list of User ID => Full Name with Full
     *              Name sanitized
     */
    public function testSanitizeUserList($userList, $expectedResult)
    {
        $viewMock = $this->getMockBuilder('\ViewPortalConfig')
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();
        $viewMock->sanitizeUserList($userList);
        $this->assertEquals($expectedResult, $userList);
    }

    public function providerSanitizeUserList(): array
    {
        return [
            [
                [
                    'userID_1' => 'Fake Name',
                    'userID_2' => 'Fakename',
                ],
                [
                    'userID_1' => 'Fake Name',
                    'userID_2' => 'Fakename',
                ],
            ],
            [
                [
                    'userID_1' => '<script>confirm(2)</script>',
                    'userID_2' => '<script>alert(3)</script>',
                ],
                [
                    'userID_1' => 'confirm(2)',
                    'userID_2' => 'alert(3)',
                ],
            ],
        ];
    }

    /**
     * @covers ::decodeConfig
     * @dataProvider providerDecodeConfig
     * @param array|string $input the config value to decode
     * @param mixed $expectedResult the expected config value after HTML decoding
     */
    public function testDecodeConfig($input, $expectedResult)
    {
        $viewMock = $this->getMockBuilder('\ViewPortalConfig')
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();
        $this->assertEquals($viewMock->decodeConfig($input), $expectedResult);
    }

    public function providerDecodeConfig(): array
    {
        return [
            [
                'enabled',
                'enabled',
            ],
            [
                'This &quot;is&quot; a &lt;b&gt;test&lt;/b&gt;',
                'This "is" a <b>test</b>',
            ],
            [
                [
                    'setting1' => 'This &quot;is&quot; a &lt;b&gt;test&lt;/b&gt;',
                    'setting2' => 'This &quot;is&quot; a &lt;b&gt;test&lt;/b&gt;',
                    'setting3' => 'This &quot;is&quot; a &lt;b&gt;test&lt;/b&gt;',
                ],
                [
                    'setting1' => 'This "is" a <b>test</b>',
                    'setting2' => 'This "is" a <b>test</b>',
                    'setting3' => 'This "is" a <b>test</b>',
                ],
            ],
        ];
    }
}
