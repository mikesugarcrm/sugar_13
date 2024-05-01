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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine\PMSEPreProcessor;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSETerminateValidator
 */
class PMSETerminateValidatorTest extends TestCase
{
    public function validateParamsRelatedProvider()
    {
        $acct = (object)['module_dir' => 'Accounts'];
        $case = (object)['module_dir' => 'Cases'];

        return [
            // Tests that the process module is the target module
            [
                'bean' => $acct,
                'flowData' => [
                    'rel_process_module' => 'Accounts',
                    'rel_element_relationship' => '',
                    'rel_element_module' => 'Accounts',
                    'pro_terminate_variables' => '',
                ],
                'expect' => [],
            ],
            // Tests target module is not process module, but bean is process module
            [
                'bean' => $acct,
                'flowData' => [
                    'rel_process_module' => 'Accounts',
                    'rel_element_relationship' => 'accounts_cases',
                    'rel_element_module' => 'Cases',
                    'pro_terminate_variables' => '',
                ],
                'expect' => [],
            ],
            // Tests target module is not process module, and bean is not process module
            [
                'bean' => $case,
                'flowData' => [
                    'rel_process_module' => 'Accounts',
                    'rel_element_relationship' => 'accounts_cases',
                    'rel_element_module' => 'Cases',
                    'pro_terminate_variables' => '',
                ],
                'expect' => [
                    'replace_fields' => [
                        'accounts_cases' => 'Cases',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::validateParamsRelated
     * @param SugarBean $bean Sugar bean
     * @param array $flowData Flow data
     * @param array $expect Expectation
     * @dataProvider validateParamsRelatedProvider
     */
    public function testValidateParamsRelated($bean, $flowData, $expect)
    {
        $logger = $this->createPartialMock('PMSELogger', ['log']);
        $logger->method('log')->willReturn(true);

        $tv = new \PMSETerminateValidator();
        $tv->setLogger($logger);
        $actual = $tv->validateParamsRelated($bean, $flowData);
        $this->assertSame($expect, $actual);
    }
}
