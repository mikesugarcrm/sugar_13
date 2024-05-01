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

namespace Sugarcrm\SugarcrmTestsUnit\inc\Entitlements;

use Exception;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\inc\Entitlements\Exception\SubscriptionException;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Entitlements\Addon;

/**
 * Class AddonTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Entitlements\Addon
 */
class AddonTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::parse
     */
    public function testGetDataWithoutID(): void
    {
        $this->expectException(SubscriptionException::class);
        new Addon('', []);
    }

    /**
     * @covers ::__construct
     * @covers ::parse
     * @covers ::__get
     * @covers ::getBundledProducts
     *
     * @dataProvider addonProvider
     */
    public function testGetData($id, $data, $expected)
    {
        $addon = new Addon($id, $data);

        // fields to check
        $fieldsToCheck = array_merge(Addon::ATTRIBUTES, ['id', 'product_code_c']);
        foreach ($fieldsToCheck as $field) {
            if (isset($expected[$field])) {
                $this->assertSame($expected[$field], $addon->$field);
            }
        }
        // invalid field name
        $this->assertEmpty($addon->xyz);

        // check bundle
        $this->assertSame($expected['bundle_count'], count($addon->getBundledProducts()));
    }

    public function addonProvider()
    {
        return [
            'non bundle data' => [
                '11d7e3f8-ed89-f588-e9af-4dbf44a9b207',
                [
                    'quantity' => '150',
                    'product_name' => 'Sugar Sell',
                    'start_date_c' => 1898582000,
                    'expiration_date' => 4102473600,
                    'product_code_c' => 'SELL',
                ],
                [
                    'id' => '11d7e3f8-ed89-f588-e9af-4dbf44a9b207',
                    'quantity' => '150',
                    'product_name' => 'Sugar Sell',
                    'start_date_c' => 1898582000,
                    'expiration_date' => 4102473600,
                    'bundle_count' => 0,
                    'product_code_c' => 'SELL',
                ],
            ],
            'bundled data' => [
                'product-template-id-for-sell-premiere',
                [
                    'quantity' => 10,
                    'product_name' => 'Sell Premiere',
                    'start_date_c' => 1639958400,
                    'product_code_c' => 'SELL',
                    'expiration_date' => 4102473600,
                    'bundled_products' => [
                        '4db82ab6-40bd-11ec-a593-06eddc549468' => [
                            'quantity' => 10,
                            'product_name' => 'Sugar Discover',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => null,
                            'customer_product_name_c' => 'Sugar Discover',
                        ],
                        'ab0fabd8-5cba-11e9-9dba-02c10f456dba' => [
                            'quantity' => 10,
                            'product_name' => 'Sugar Connect',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => '',
                            'customer_product_name_c' => 'Sugar Connect',
                        ],
                    ],
                    'product_edition_c' => 'SELL_PREMIERE',
                    'deployment_flavor_c' => 'Ent',
                    'customer_product_name_c' => 'Sell Premiere',
                ],
                [
                    'id' => 'product-template-id-for-sell-premiere',
                    'quantity' => 10,
                    'product_name' => 'Sell Premiere',
                    'start_date_c' => 1639958400,
                    'product_code_c' => 'SELL',
                    'expiration_date' => 4102473600,
                    'product_edition_c' => 'SELL_PREMIERE',
                    'deployment_flavor_c' => 'Ent',
                    'customer_product_name_c' => 'Sell Premiere',
                    'bundle_count' => 2,
                ],
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::parse
     *
     * @dataProvider addonExceptionProvider
     */
    public function testGetDataException($id, $data)
    {
        $this->expectException(Exception::class);
        new Addon($id, $data);
    }

    public function addonExceptionProvider()
    {
        return [
            [
                '',
                [
                    'quantity' => '150',
                    'product_name' => 'iPad with offline sync',
                    'expiration_date' => 4102473600,
                ],
            ],
        ];
    }

    /**
     *
     * @covers ::isValidBundle
     *
     * @dataProvider bundleProvider
     */
    public function testIsValidBundle($id, $data, $expected)
    {
        $addon = new Addon($id, $data);

        // check bundle
        $this->assertSame($expected, $addon->isValidBundle());
    }

    public function bundleProvider(): array
    {
        return [
            'empty bundle data' => [
                '11d7e3f8-ed89-f588-e9af-4dbf44a9b207',
                [
                    'quantity' => '150',
                    'product_name' => 'Sugar Sell',
                    'start_date_c' => 1898582000,
                    'expiration_date' => 4102473600,
                    'product_code_c' => 'SELL',
                ],
                true,
            ],
            'valid bundled data' => [
                'product-template-id-for-sell-premiere',
                [
                    'quantity' => 10,
                    'product_name' => 'Sell Premiere',
                    'start_date_c' => 1639958400,
                    'product_code_c' => 'SELL',
                    'expiration_date' => 4102473600,
                    'bundled_products' => [
                        '4db82ab6-40bd-11ec-a593-06eddc549468' => [
                            'quantity' => 10,
                            'product_name' => 'Sugar Discover',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => null,
                            'customer_product_name_c' => 'Sugar Discover',
                        ],
                        'ab0fabd8-5cba-11e9-9dba-02c10f456dba' => [
                            'quantity' => 10,
                            'product_name' => 'Sugar Connect',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => '',
                            'customer_product_name_c' => 'Sugar Connect',
                        ],
                    ],
                    'product_edition_c' => 'SELL_PREMIERE',
                    'deployment_flavor_c' => 'Ent',
                    'customer_product_name_c' => 'Sell Premiere',
                ],
                true,
            ],
            'empty quanity in bundled data' => [
                'product-template-id-for-sell-premiere',
                [
                    'quantity' => 10,
                    'product_name' => 'Sell Premiere',
                    'start_date_c' => 1639958400,
                    'product_code_c' => 'SELL',
                    'expiration_date' => 4102473600,
                    'bundled_products' => [
                        '4db82ab6-40bd-11ec-a593-06eddc549468' => [
                            'product_name' => 'Sugar Discover',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => null,
                            'customer_product_name_c' => 'Sugar Discover',
                        ],
                        'ab0fabd8-5cba-11e9-9dba-02c10f456dba' => [
                            'product_name' => 'Sugar Connect',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => '',
                            'customer_product_name_c' => 'Sugar Connect',
                        ],
                    ],
                    'product_edition_c' => 'SELL_PREMIERE',
                    'deployment_flavor_c' => 'Ent',
                    'customer_product_name_c' => 'Sell Premiere',
                ],
                true,
            ],
            'different quanity bundled data' => [
                'product-template-id-for-sell-premiere',
                [
                    'quantity' => 10,
                    'product_name' => 'Sell Premiere',
                    'start_date_c' => 1639958400,
                    'product_code_c' => 'SELL',
                    'expiration_date' => 4102473600,
                    'bundled_products' => [
                        '4db82ab6-40bd-11ec-a593-06eddc549468' => [
                            'quantity' => 1011,
                            'product_name' => 'Sugar Discover',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => null,
                            'customer_product_name_c' => 'Sugar Discover',
                        ],
                        'ab0fabd8-5cba-11e9-9dba-02c10f456dba' => [
                            'quantity' => 102,
                            'product_name' => 'Sugar Connect',
                            'start_date_c' => 1639958400,
                            'product_code_c' => null,
                            'expiration_date' => 4102473600,
                            'product_edition_c' => '',
                            'deployment_flavor_c' => '',
                            'customer_product_name_c' => 'Sugar Connect',
                        ],
                    ],
                    'product_edition_c' => 'SELL_PREMIERE',
                    'deployment_flavor_c' => 'Ent',
                    'customer_product_name_c' => 'Sell Premiere',
                ],
                false,
            ],
        ];
    }
}
