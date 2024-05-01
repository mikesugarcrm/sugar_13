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

namespace Sugarcrm\SugarcrmTestsUnit\Filters\Operand;

use BeanFactory;
use ServiceBase;
use SugarApiExceptionInvalidParameter;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants
 */
class EmailParticipantsTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testUnrecognizedOperand()
    {
        $filter = [
            [
                'parent_type' => 'Contacts',
                'parent_id' => 'abc',
            ],
        ];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        new EmailParticipants('$foo', $filter);
    }

    public function operandProvider()
    {
        return [
            'operand: $from' => [
                '$from',
                'from',
            ],
            'operand: $to' => [
                '$to',
                'to',
            ],
            'operand: $cc' => [
                '$cc',
                'cc',
            ],
            'operand: $bcc' => [
                '$bcc',
                'bcc',
            ],
        ];
    }

    /**
     * @covers ::apiUnserialize
     * @dataProvider operandProvider
     */
    public function testApiUnserialize(string $op, string $link)
    {
        $filter = [
            [
                '_link' => $link,
                'parent_type' => 'Contacts',
                'parent_id' => 'abc',
                'parent_name' => 'Joe Walsh',
                'parent' => [
                    'type' => 'Contacts',
                    'id' => 'abc',
                    'name' => 'Joe Walsh',
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
                'email_address_id' => 'ghi',
                'email_address' => 'jwalsh@example.com',
                'email_addresses' => [
                    'id' => 'ghi',
                    'email_address' => 'jwalsh@example.com',
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
            ],
            [
                '_link' => $link,
                'parent_type' => 'Contacts',
                'parent_id' => 'def',
                'parent_name' => 'Tommy Hunter',
                'parent' => [
                    'type' => 'Contacts',
                    'id' => 'def',
                    'name' => 'Tommy Hunter',
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
                'email_address_id' => 'jkl',
                'email_address' => 'thunter@example.com',
                'email_addresses' => [
                    'id' => 'jkl',
                    'email_address' => 'thunter@example.com',
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
            ],
        ];

        $api = $this->getMockForAbstractClass(ServiceBase::class);
        $operand = new EmailParticipants($op, $filter);

        $actual = $operand->apiUnserialize($api);

        $expected = [
            [
                'parent_type' => 'Contacts',
                'parent_id' => 'abc',
                'email_address_id' => 'ghi',
            ],
            [
                'parent_type' => 'Contacts',
                'parent_id' => 'def',
                'email_address_id' => 'jkl',
            ],
        ];
        $this->assertEquals($expected, $actual);
    }
}
