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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarFields\Fields\Fullname;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarFieldFullname
 */
class SugarFieldFullnameTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        parent::tearDown();
    }

    public function isErasedProvider()
    {
        $user = $this->createPartialMock('\\User', ['getPreference']);
        $user->method('getPreference')
            ->with($this->equalTo('default_locale_name_format'))
            ->willReturn('f l');

        return [
            'Current user, no erased are fields' => [
                $user,
                [
                    'first_name' => 'Bill',
                    'last_name' => 'Hart',
                    'salutation' => 'Mr.',
                ],
                [],
                false,
            ],
            'Current user, one field is erased' => [
                $user,
                [
                    'first_name' => '',
                    'last_name' => 'Hart',
                    'salutation' => 'Mr.',
                ],
                [
                    'first_name',
                ],
                false,
            ],
            'Current user, all fields are erased' => [
                $user,
                [
                    'first_name' => '',
                    'last_name' => '',
                    'salutation' => '',
                ],
                [
                    'first_name',
                    'last_name',
                    'salutation',
                ],
                true,
            ],
            'Current user, one field is erased and all are empty' => [
                $user,
                [
                    'first_name' => '',
                    'last_name' => '',
                    'salutation' => '',
                ],
                [
                    'last_name',
                ],
                true,
            ],
            'Current user, first_name and last_name are erased but salutation is not in the format' => [
                $user,
                [
                    'first_name' => '',
                    'last_name' => '',
                    'salutation' => 'Mr.',
                ],
                [
                    'first_name',
                    'last_name',
                ],
                true,
            ],
            'No current user, no erased are fields' => [
                null,
                [
                    'first_name' => 'Bill',
                    'last_name' => 'Hart',
                    'salutation' => 'Mr.',
                ],
                [],
                false,
            ],
            'No current user, one field is erased' => [
                null,
                [
                    'first_name' => '',
                    'last_name' => 'Hart',
                    'salutation' => 'Mr.',
                ],
                [
                    'first_name',
                ],
                false,
            ],
            'No current user, all fields are erased' => [
                null,
                [
                    'first_name' => '',
                    'last_name' => '',
                    'salutation' => '',
                ],
                [
                    'first_name',
                    'last_name',
                    'salutation',
                ],
                true,
            ],
            'No current user, one field is erased and all are empty' => [
                null,
                [
                    'first_name' => '',
                    'last_name' => '',
                    'salutation' => '',
                ],
                [
                    'last_name',
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider isErasedProvider
     * @covers ::isErased
     * @covers       \Localization::getNameFormatFields
     * @covers       \Localization::getBean
     * @covers       \Localization::getLocaleFormatMacro
     * @covers       \Localization::parseLocaleFormatMacro
     */
    public function testIsErased($user, $data, $erasedFields, $expected)
    {
        $GLOBALS['current_user'] = $user;

        $bean = $this->createPartialMock('\\Contact', ['getFieldDefinition']);
        $bean->method('getFieldDefinition')->willReturn([
            'name' => 'name',
            'vname' => 'LBL_NAME',
            'type' => 'fullname',
            'fields' => [
                'first_name',
                'last_name',
                'salutation',
                'title',
            ],
            'sort_on' => 'last_name',
            'source' => 'non-db',
            'group' => 'last_name',
            'db_concat_fields' => [
                'first_name',
                'last_name',
            ],
            'importable' => 'false',
        ]);
        $bean->name_format_map = [
            'f' => 'first_name',
            'l' => 'last_name',
            's' => 'salutation',
            't' => 'title',
        ];
        $bean->erased_fields = $erasedFields;

        foreach ($data as $key => $value) {
            $bean->{$key} = $value;
        }

        $field = new \SugarFieldFullname('fullname');
        $actual = $field->isErased($bean, 'name');

        $this->assertSame($expected, $actual);
    }
}
