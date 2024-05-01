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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Subject;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Util\Uuid;
use User as SugarUser;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Subject\User
 */
class UserTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::jsonSerialize
     * @uses \Sugarcrm\Sugarcrm\Security\Subject\ApiClient
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $userId = Uuid::uuid1();

        $sugarUser = $this->createMock(SugarUser::class);
        $sugarUser->id = $userId;

        $client = $this->createMock(ApiClient::class);
        $client->method('jsonSerialize')
            ->willReturn([
                'type' => 'mock-api',
            ]);
        $user = new User($sugarUser, $client);

        $this->assertSame([
            '_type' => 'user',
            'id' => $userId,
            '_module' => 'Users',
            'client' => [
                'type' => 'mock-api',
            ],
        ], $user->jsonSerialize());
    }
}
