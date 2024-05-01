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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Listener\Success\OIDC;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\OIDC\UpdateUserLanguageListener;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * Class UpdateUserLanguageListenerTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\OIDC\UpdateUserLanguageListener
 */
class UpdateUserLanguageListenerTest extends TestCase
{
    /**
     * @var UpdateUserLanguageListener
     */
    protected $listener;

    /**
     * @var UsernamePasswordToken|MockObject
     */
    protected $token;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * @var User|MockObject
     */
    protected $user;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sugarUser = $this->createMock(\User::class);
        $this->token = $this->createMock(UsernamePasswordToken::class);
        $this->event = new AuthenticationEvent($this->token);
        $this->listener = new UpdateUserLanguageListener();
        $this->user = $this->createMock(User::class);

        $this->token->method('getUser')->willReturn($this->user);
        $this->user->method('getSugarUser')->willReturn($this->sugarUser);
    }

    /**
     * @return array
     */
    public function executeProviderLanguageShouldNotChange(): array
    {
        return [
            'noLanguageInToken' => [
                'oidcData' => [],
                'sugarLanguage' => 'en_us',
            ],
            'emptyLanguageInToken' => [
                'oidcData' => [
                    'preferred_language' => '',
                ],
                'sugarLanguage' => 'en_us',
            ],
            'sameLanguageInSugarAndInToken' => [
                'oidcData' => [
                    'preferred_language' => 'de_DE',
                ],
                'sugarLanguage' => 'de_DE',
            ],
        ];
    }

    /**
     * @param $oidcData
     * @param $sugarLanguage
     *
     * @dataProvider executeProviderLanguageShouldNotChange
     *
     * @covers ::execute
     */
    public function testExecuteLanguageShouldNotChange($oidcData, $sugarLanguage): void
    {
        $this->user->expects($this->once())
            ->method('getAttribute')
            ->with('oidc_data')
            ->willReturn($oidcData);
        $this->sugarUser->preferred_language = $sugarLanguage;
        $this->sugarUser->expects($this->never())->method('save');

        $this->listener->execute($this->event);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteLanguageShouldSave(): void
    {
        $this->user->expects($this->once())
            ->method('getAttribute')
            ->with('oidc_data')
            ->willReturn(['preferred_language' => 'de_DE']);
        $this->sugarUser->preferred_language = 'en_US';
        $this->sugarUser->expects($this->once())->method('save');

        $this->listener->execute($this->event);

        $this->assertEquals('de_DE', $this->sugarUser->preferred_language);
    }
}
