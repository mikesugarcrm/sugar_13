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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\User\Mapping;

use OneLogin\Saml2\Response;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\Mapping\SugarSAMLUserMapping;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\Mapping\SugarSAMLUserMapping
 */
class SugarSAMLUserMappingTest extends TestCase
{
    /**
     * @var Response
     */
    protected $samlResponse;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->samlResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers ::map
     */
    public function testMapHasCreateAndUpdateSections()
    {
        $mapper = new SugarSAMLUserMapping([]);
        $result = $mapper->map($this->samlResponse);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('create', $result['attributes']);
        $this->assertArrayHasKey('update', $result['attributes']);
    }

    /**
     * @return array
     */
    public function mapDataProvider()
    {
        return [
            'empty config' => [
                [],
                [
                    'attr1' => ['foo'],
                ],
                [
                    'attributes' => [
                        'create' => [],
                        'update' => [],
                    ],
                ],
            ],
            'missing response attributes' => [
                [
                    'sp' => [
                        'sugarCustom' => [
                            'saml2_settings' => [
                                'create' => ['user_name' => 'attr1', 'last_name' => 'attr2'],
                            ],
                        ],
                    ],
                ],
                [],
                [
                    'attributes' => [
                        'create' => [],
                        'update' => [],
                    ],
                ],
            ],
            'config and response attributes are present' => [
                [
                    'sp' => [
                        'sugarCustom' => [
                            'saml2_settings' => [
                                'create' => [
                                    'user_name' => 'attr1',
                                    'first_name' => 'attr2',
                                    'last_name' => 'attr4',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'attr1' => ['foo'],
                    'attr2' => [123],
                    'attr3' => ['bar'],
                ],
                [
                    'attributes' => [
                        'create' => [
                            'user_name' => 'foo',
                            'first_name' => 123,
                        ],
                        'update' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::map
     * @dataProvider mapDataProvider
     *
     * @param array $config
     * @param array $responseAttributes
     * @param array $expected
     */
    public function testMap($config, $responseAttributes, $expected)
    {
        $mapper = new SugarSAMLUserMapping($config);

        $this->samlResponse->method('getAttributes')->willReturn($responseAttributes);

        $result = $mapper->map($this->samlResponse);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::map
     */
    public function testMapCanUseXpath()
    {
        $config = [
            'sp' => [
                'sugarCustom' => [
                    'useXML' => true,
                    'saml2_settings' => [
                        'create' => [
                            'user_name' => '//some/path',
                        ],
                    ],
                ],
            ],
        ];

        $mapper = $this->getMockBuilder(SugarSAMLUserMapping::class)
            ->setMethods(['getDOMXPath'])
            ->setConstructorArgs([$config])
            ->getMock();

        $nodeList = $this->createMock('DOMNodeList');

        $this->samlResponse->expects($this->once())->method('getXMLDocument');

        $domXpath = $this->createMock('DOMXpath');
        $domXpath->expects($this->once())->method('query')->with('//some/path')->willReturn($nodeList);

        $mapper->expects($this->any())->method('getDOMXPath')->willReturn($domXpath);

        $mapper->map($this->samlResponse);
    }

    /**
     * @return array
     */
    public function mapIdentitySetsFieldDataProvider()
    {
        return [
            'empty config' => [
                [],
                'email',
            ],
            'id is set in custom settings' => [
                [
                    'sp' => [
                        'sugarCustom' => [
                            'id' => 'last_name',
                        ],
                    ],
                ],
                'last_name',
            ],
        ];
    }

    /**
     * @covers ::mapIdentity
     * @dataProvider mapIdentitySetsFieldDataProvider
     *
     * @param array $config
     * @param string $expected
     */
    public function testMapIdentitySetsField($config, $expected)
    {
        $mapper = new SugarSAMLUserMapping($config);

        $result = $mapper->mapIdentity($this->samlResponse);
        $this->assertArrayHasKey('field', $result);
        $this->assertEquals($expected, $result['field']);
    }

    /**
     * @return array
     */
    public function mapIdentitySetsValueDataProvider()
    {
        return [
            'empty config and response attributes' => [
                [],
                [],
                'test@test.com',
                'test@test.com',
            ],
            'empty config' => [
                [],
                ['attr1' => ['test2@test.com']],
                'test@test.com',
                'test@test.com',
            ],
            'attributes do not contain search field' => [
                [
                    'sp' => [
                        'sugarCustom' => [
                            'saml2_settings' => [
                                'check' => [
                                    'user_name' => 'attr2',
                                ],
                            ],
                        ],
                    ],
                ],
                ['attr1' => ['foo@bar.com']],
                'test@test.com',
                'test@test.com',
            ],
            'attributes contain search field' => [
                [
                    'sp' => [
                        'sugarCustom' => [
                            'saml2_settings' => [
                                'check' => [
                                    'user_name' => 'attr2',
                                ],
                            ],
                        ],
                    ],
                ],
                ['attr1' => ['foo@bar.com'], 'attr2' => ['baz@example.com']],
                'test@test.com',
                'baz@example.com',
            ],
        ];
    }

    /**
     * @covers ::mapIdentity
     * @dataProvider mapIdentitySetsValueDataProvider
     *
     * @param array $config
     * @param array $responseAttributes
     * @param string $responseNameId
     * @param string $expected
     */
    public function testMapIdentitySetsValue($config, $responseAttributes, $responseNameId, $expected)
    {
        $mapper = new SugarSAMLUserMapping($config);

        $this->samlResponse->method('getAttributes')->willReturn($responseAttributes);
        $this->samlResponse->method('getNameId')->willReturn($responseNameId);

        $result = $mapper->mapIdentity($this->samlResponse);
        $this->assertArrayHasKey('value', $result);
        $this->assertEquals($expected, $result['value']);
    }
}
