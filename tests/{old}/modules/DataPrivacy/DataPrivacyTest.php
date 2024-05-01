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

class DataPrivacyTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider relatedRecordRemovedProvider
     *
     * @param array $inputFieldsToErase
     * @param bool $shouldSave
     * @param string $link
     * @param string $id
     * @param array $expectedFieldsToErase
     */
    public function testRelatedRecordRemoved(
        array  $inputFieldsToErase,
        bool   $shouldSave,
        string $link,
        string $id,
        array  $expectedFieldsToErase
    ) {

        $mockDp = $this->getMockBuilder('\DataPrivacy')->disableOriginalConstructor()->setMethods(['save'])->getMock();
        if ($shouldSave) {
            $mockDp->expects($this->once())->method('save');
        } else {
            $mockDp->expects($this->never())->method('save');
        }
        $mockDp->fields_to_erase = json_encode($inputFieldsToErase);
        $mockDp->relatedRecordRemoved($link, $id);

        $this->assertSame($mockDp->fields_to_erase, json_encode($expectedFieldsToErase));
    }

    public function relatedRecordRemovedProvider()
    {
        return [
            [
                [
                    'contacts' => [
                        'cid1' => ['foo', 'bar'],
                    ],
                ],
                true,
                'contacts',
                'cid1',
                ['contacts' => []],
            ],
            [
                [
                    'contacts' => [
                        'cid1' => ['foo', 'bar'],
                        'cid2' => ['foo'],
                    ],
                    'leads' => ['lid1' => ['foo']],
                ],
                true,
                'contacts',
                'cid1',
                [
                    'contacts' => ['cid2' => ['foo']],
                    'leads' => ['lid1' => ['foo']],
                ],
            ],
            [
                [
                    'contacts' => [
                        'cid1' => ['foo', 'bar'],
                        'cid2' => ['foo'],
                    ],
                    'leads' => ['lid1' => ['foo']],
                ],
                false,
                'contacts',
                'cid3',
                [
                    'contacts' => [
                        'cid1' => ['foo', 'bar'],
                        'cid2' => ['foo'],
                    ],
                    'leads' => ['lid1' => ['foo']],
                ],
            ],
            [
                [
                    'contacts' => [
                        'cid1' => ['foo', 'bar'],
                        'cid2' => ['foo'],
                    ],
                    'leads' => ['lid1' => ['foo']],
                ],
                false,
                'not_a_link',
                '1234',
                [
                    'contacts' => [
                        'cid1' => ['foo', 'bar'],
                        'cid2' => ['foo'],
                    ],
                    'leads' => ['lid1' => ['foo']],
                ],
            ],
        ];
    }
}
