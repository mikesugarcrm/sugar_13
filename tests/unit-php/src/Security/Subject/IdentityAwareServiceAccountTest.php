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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\ServiceAccount;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient;
use Sugarcrm\Sugarcrm\Security\Subject\IdentityAwareServiceAccount;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Subject\IdentityAwareServiceAccount
 */
class IdentityAwareServiceAccountTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        $identityAwareServiceAccount = 'srn:cluster:iam::0000000001:sa:service_account_id';
        $serviceAccount = new ServiceAccount();
        $serviceAccount->setSrn($identityAwareServiceAccount);

        $client = $this->createMock(ApiClient::class);
        $client->method('jsonSerialize')->willReturn(['type' => 'mock-api']);
        $user = new IdentityAwareServiceAccount($serviceAccount, $client);

        $this->assertSame([
            '_type' => 'identity-aware-sa',
            'id' => $identityAwareServiceAccount,
            'client' => [
                'type' => 'mock-api',
            ],
        ], $user->jsonSerialize());
    }
}
