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

class EmailSearchTest extends TestCase
{
    /** @var EmailUI */
    private $emailUI;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->emailUI = new EmailUI();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider buildWhereAddListProvider
     * @param array $terms
     * @param array $expected
     * @return void
     */
    public function testBuildWhereAddList(array $terms, array $expected): void
    {
        $whereAdds = SugarTestReflection::callProtectedMethod($this->emailUI, 'buildWhereAddList', [$terms]);
        $this->assertEqualsCanonicalizing($expected, $whereAdds);
    }

    public function buildWhereAddListProvider()
    {
        return [
            'empty search' => [[], ['']],
            'first name, one word' => [
                ['first_name' => 'example'],
                ["first_name LIKE 'example%'"],
            ],
            'first name, two words' => [
                ['first_name' => 'another example'],
                ["first_name LIKE 'another example%'"],
            ],
            'first and last name, one word' => [
                ['first_name' => 'example', 'last_name' => 'example'],
                [
                    "first_name LIKE 'example%'",
                    "last_name LIKE 'example%'",
                ],
            ],
            'first, last and full name, one word' => [
                ['first_name' => 'example', 'last_name' => 'example', 'full_name' => 'example'],
                [
                    "first_name LIKE 'example%'",
                    "last_name LIKE 'example%'",
                ],
            ],
            'first and last name, one word; full name, different word' => [
                ['first_name' => 'example', 'last_name' => 'example', 'full_name' => 'test'],
                [
                    "first_name LIKE 'example%'",
                    "first_name LIKE 'test%'",
                    "last_name LIKE 'example%'",
                    "last_name LIKE 'test%'",
                ],
            ],
            'first, last and full name, two words' => [
                ['first_name' => 'another example', 'last_name' => 'another example', 'full_name' => 'another example'],
                [
                    "first_name LIKE 'another example%'",
                    "last_name LIKE 'another example%'",
                    "first_name = 'another' AND last_name LIKE 'example%'",
                    "first_name LIKE 'another%' AND last_name = 'example'",
                    "first_name = 'example' AND last_name LIKE 'another%'",
                    "first_name LIKE 'example%' AND last_name = 'another'",
                ],
            ],
            'first, last and full name, three words' => [
                ['first_name' => 'three words example', 'last_name' => 'three words example', 'full_name' => 'three words example'],
                [
                    "first_name LIKE 'three words example%'",
                    "last_name LIKE 'three words example%'",
                    "first_name = 'three' AND last_name LIKE 'words example%'",
                    "first_name LIKE 'three%' AND last_name = 'words example'",
                    "first_name = 'words example' AND last_name LIKE 'three%'",
                    "first_name LIKE 'words example%' AND last_name = 'three'",
                    "first_name = 'three words' AND last_name LIKE 'example%'",
                    "first_name LIKE 'three words%' AND last_name = 'example'",
                    "first_name = 'example' AND last_name LIKE 'three words%'",
                    "first_name LIKE 'example%' AND last_name = 'three words'",
                ],
            ],
        ];
    }
}
