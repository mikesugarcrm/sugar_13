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

namespace Sugarcrm\SugarcrmTestsUnit\Config;

use PHPUnit\Framework\TestCase;

class ConfigMaskingTest extends TestCase
{
    /**
     * @covers ::getConfigWithMaskedPasswords
     * @covers ::maskConfigNode
     * @dataProvider getSugarConfig
     */
    public function testGetConfigWithMaskedPasswords(array $config, array $expectedDiff): void
    {
        $maskedConfig = getConfigWithMaskedPasswords($config);

        $diff = deepArrayDiff($maskedConfig, $config);

        $this->assertEquals($diff, $expectedDiff);
    }

    public function getSugarConfig(): array
    {
        return [
            'config without passwords' => [
                'config' => [
                    'test_field' => 'test',
                    //Fields don't end with "password|pwd|pass" key
                    'test_password_setting' => 'test_password_setting',
                    'test_pwd_setting' => 'test_pwd_setting',
                    'test_pass_setting' => 'test_pass_setting',
                    //Field ends with "password" key but it's not leaf node that's why we don't
                    //mask it
                    'test_password' => [
                        'test_password_inner' => 'test_password_inner',
                    ],
                ],
                'expectedDiff' => [],
            ],
            'config with passwords' => [
                'config' => [
                    'test_field' => 'test',
                    //Should be masked because it's leaf node which ends with "password"
                    'test_password' => 'test_password',
                    'test_inner' => [
                        //Should be masked because it's leaf node which ends with "pwd"
                        'test_pwd' => 'test_pwd',
                        'test_inner_inner' => [
                            //Should be masked because it's leaf node which ends with "pass"
                            'test_pass' => 'test_pass',
                        ],
                    ],
                ],
                'expectedDiff' => [
                    'test_password' => '******',
                    'test_inner' => [
                        'test_pwd' => '******',
                        'test_inner_inner' => [
                            'test_pass' => '******',
                        ],
                    ],
                ],
            ],
        ];
    }
}
