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
 * Tests RelatedValueApi.
 */
class RelatedValueApiTest extends TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var SugarBean
     */
    protected $account;

    /**
     * @var SugarBean
     */
    protected $contact;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
        $this->api = new RelatedValueApi();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact('', ['first_name' => 'RS148Test_FName']);
        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact);
        $this->account->save();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * @param array $fields
     * @param mixed $expected
     * @param bool $setRelatedId
     * @param bool $skipEncoding
     * @dataProvider relatedProvider
     */
    public function testRelatedValue($fields, $expected, $setRelatedId, $skipEncoding = false)
    {
        if ($setRelatedId) {
            $fields['contacts']['relId'] = $this->contact->id;
            $expected['contacts']['relId'] = $this->contact->id;
        }
        if (empty($skipEncoding)) {
            $fields = json_encode($fields);
        }
        $result = $this->api->getRelatedValues(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'fields' => $fields, 'record' => $this->account->id]
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the related value API where the fields argument is an array, not an
     * encoded URL (i.e. using the POST endpoint)
     * @param $fields
     * @param $expected
     * @param $setRelatedId
     * @dataProvider relatedProvider
     */
    public function testRelatedValuePost($fields, $expected, $setRelatedId)
    {
        $this->testRelatedValue($fields, $expected, $setRelatedId, true);
    }

    public function relatedProvider()
    {
        return [
            'empty' => [
                [],
                [],
                false,
            ],
            'related' => [
                [
                    'contacts' => [
                        'link' => 'contacts',
                        'type' => 'related',
                        'relate' => 'first_name',
                    ],
                ],
                [
                    'contacts' => [
                        'related' => [
                            'first_name' => 'RS148Test_FName',
                        ],
                    ],
                ],
                true,
            ],
            'count' => [
                [
                    'contacts' => [
                        'link' => 'contacts',
                        'type' => 'count',
                        'relate' => 'first_name',
                    ],
                ],
                [
                    'contacts' => [
                        'count' => 1,
                    ],
                ],
                false,
            ],
            'rollupMin' => [
                [
                    'contacts' => [
                        'link' => 'contacts',
                        'type' => 'rollupMin',
                        'relate' => 'first_name',
                    ],
                ],
                [
                    'contacts' => [
                        'rollupMin' => [
                            'first_name' => '',
                            'first_name_values' => [],
                        ],
                    ],
                ],
                false,
            ],
            'rollupSum' => [
                [
                    'contacts' => [
                        'link' => 'contacts',
                        'type' => 'rollupSum',
                        'relate' => 'first_name',
                    ],
                ],
                [
                    'contacts' => [
                        'rollupSum' => [
                            'first_name' => 0,
                            'first_name_values' => [],
                        ],
                    ],
                ],
                false,
            ],
        ];
    }

    public function testRelatedValueWithCurrencyWillConvertToBase()
    {
        $mock_opp = $this->getMockBuilder('Opportunity')
            ->disableOriginalConstructor()
            ->setMethods(['getFieldDefinition'])
            ->getMock();

        $mock_opp->expects($this->once())
            ->method('getFieldDefinition')
            ->willReturn(['type' => 'currency']);

        $mock_opp->base_rate = '0.9';
        $mock_opp->amount = '90.000000';

        $mock_opp->field_defs = [
            'amount' => [
                'type' => 'currency',
            ],
        ];

        $mock_acc = $this->getMockBuilder('Account')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationship'])
            ->getMock();

        $mock_acc->expects($this->once())
            ->method('load_relationship')
            ->willReturn(true);

        $mock_acc->id = 'test';


        $mock_acc_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeansForSugarLogic'])
            ->getMock();

        $mock_acc_link2->expects($this->once())
            ->method('getBeansForSugarLogic')
            ->willReturn([$mock_opp]);

        $mock_acc->opportunities = $mock_acc_link2;

        $mock_api = $this->getMockBuilder('RelatedValueApi')
            ->disableOriginalConstructor()
            ->setMethods(['loadBean'])
            ->getMock();

        $mock_api->expects($this->once())
            ->method('loadBean')
            ->willReturn($mock_acc);

        $fields = [
            'opportunities' => [
                'link' => 'opportunities',
                'type' => 'rollupSum',
                'relate' => 'amount',
            ],
        ];

        $actual = $mock_api->getRelatedValues(
            SugarTestRestUtilities::getRestServiceMock(),
            ['module' => 'Accounts', 'fields' => json_encode($fields), 'record' => $mock_acc->id]
        );

        $expected = [
            'opportunities' => [
                'rollupSum' => [
                    'amount' => '100.000000',
                    'amount_values' => [
                        '' => '100.000000',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }
}
