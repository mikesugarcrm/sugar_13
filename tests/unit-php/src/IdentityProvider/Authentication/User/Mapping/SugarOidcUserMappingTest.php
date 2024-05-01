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

use PHPUnit\Framework\TestCase;
use Sugarcrm\IdentityProvider\Authentication\User as IdmUser;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\Mapping\SugarOidcUserMapping;
use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\Mapping\SugarOidcUserMapping
 */
class SugarOidcUserMappingTest extends TestCase
{
    /**
     * @var SugarOidcUserMapping
     */
    protected $userMapper;

    /**
     * @var array
     */
    protected $sugarConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->userMapper = new SugarOidcUserMapping();

        if (isset($GLOBALS['sugar_config'])) {
            $this->sugarConfig = $GLOBALS['sugar_config'];
        }
        $GLOBALS['sugar_config']['languages'] = ['de_DE' => 'de_DE', 'en_us' => 'en_us', 'it_it' => 'it_it'];
        $GLOBALS['sugar_config']['disabled_languages'] = 'bg_BG,da_DK';
        Validator::clearValidatorsCache();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $GLOBALS['sugar_config'] = $this->sugarConfig;
    }

    /**
     * @return array
     */
    public function mapProvider(): array
    {
        return [
            'simpleMapping' => [
                'source' => [
                    'preferred_username' => 'username',
                    'status' => 0,
                    'address' => [
                        'street_address' => 'street',
                    ],
                ],
                'expectedMapping' => [
                    'user_name' => 'username',
                    'status' => User::USER_STATUS_ACTIVE,
                    'address_street' => 'street',
                ],
            ],
            'statusMapping' => [
                'source' => [
                    'status' => 1,
                ],
                'expectedMapping' => [
                    'status' => User::USER_STATUS_INACTIVE,
                ],
            ],
            'existingLanguageMapping' => [
                'source' => [
                    'locale' => 'de-DE',
                ],
                'expectedMapping' => [
                    'preferred_language' => 'de_DE',
                ],
            ],
            'existingLanguageMappingLocaleTwoLetters' => [
                'source' => [
                    'locale' => 'de',
                ],
                'expectedMapping' => [
                    'preferred_language' => 'de_DE',
                ],
            ],
            'enUSLanguageMapping' => [
                'source' => [
                    'locale' => 'en-US',
                ],
                'expectedMapping' => [
                    'preferred_language' => 'en_us',
                ],
            ],
            'itITLanguageMapping' => [
                'source' => [
                    'locale' => 'it-IT',
                ],
                'expectedMapping' => [
                    'preferred_language' => 'it_it',
                ],
            ],
            'notExistingLanguageMapping' => [
                'source' => [
                    'locale' => 'rt_RT',
                ],
                'expectedMapping' => [],
            ],
            'disabledLanguageMapping' => [
                'source' => [
                    'locale' => 'bg_BG',
                ],
                'expectedMapping' => [],
            ],
        ];
    }

    /**
     * @param array $source
     * @param array $expectedMapping
     *
     * @dataProvider mapProvider
     *
     * @covers ::map
     */
    public function testMap(array $source, array $expectedMapping): void
    {
        $result = $this->userMapper->map($source);
        $this->assertEquals($expectedMapping, $result);
    }

    public function providerMapIdentityException()
    {
        return [
            [''],
            [[]],
            [['sub' => 'srn:cluster:iam:eu:0000000001:tenant']],
            [['sub' => 'srn:cluster:iam:eu:0000000001:user:']],
        ];
    }

    /**
     * @dataProvider providerMapIdentityException
     * @param array $response
     * @covers ::mapIdentity
     */
    public function testMapIdentityException($response)
    {
        $this->expectException(UsernameNotFoundException::class);
        $this->userMapper->mapIdentity($response);
    }

    /**
     * @covers ::mapIdentity
     */
    public function testMapIdentity()
    {
        $identity = $this->userMapper->mapIdentity(['sub' => 'srn:cluster:iam:eu:0000000001:user:seed_sally_id']);
        $this->assertEquals('id', $identity['field']);
        $this->assertEquals('seed_sally_id', $identity['value']);
    }

    /**
     * @covers ::getIdentityValue
     */
    public function testGetIdentityValue()
    {
        $user = new IdmUser();
        $user->setSrn('srn:cluster:iam:eu:0000000001:user:seed_sally_id');
        $identity = $this->userMapper->getIdentityValue($user);
        $this->assertEquals('seed_sally_id', $identity);
    }
}
