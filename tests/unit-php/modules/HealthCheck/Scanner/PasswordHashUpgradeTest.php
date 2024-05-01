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

namespace Sugarcrm\SugarcrmTestsUnit\modules\HealthCheck\Scanner;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\modules\HealthCheck\Scanner\Checks\PasswordHashAlgo as PasswordHashAlgoCheck;
use WebUpgrader;

// cannot put in src/ and rely on autoloader, because autoloader works in context of checked instance, not HC
require_once 'modules/HealthCheck/Scanner/Checks/PasswordHashAlgo.php';

class PasswordHashUpgradeTest extends TestCase
{
    public function testNoWarningWithUpgrader(): void
    {
        $upgrader = new WebUpgrader('test');
        $upgrader->context['versionInfo'] = ['14.1.2'];
        $upgrader->config = [];

        $check = new PasswordHashAlgoCheck();

        $this->assertEquals(true, $check->check($upgrader));
        $this->assertEquals(false, $check->isError);
    }

    public function testWarningWithUpgrader(): void
    {
        $upgrader = new WebUpgrader('test');
        $upgrader->context['versionInfo'] = ['14.1.2'];
        $upgrader->config = [
            'passwordHash' => [
                'backend' => 'sha2',
            ],
        ];

        $check = new PasswordHashAlgoCheck();

        $this->assertEquals(false, $check->check($upgrader));
        $this->assertEquals(false, $check->isError);
    }

    public function testErrorWithUpgrader(): void
    {
        $upgrader = new WebUpgrader('test');
        $upgrader->context['versionInfo'] = ['14.2.1'];
        $upgrader->config = [
            'passwordHash' => [
                'backend' => 'native',
            ],
        ];

        $check = new PasswordHashAlgoCheck();

        $this->assertEquals(false, $check->check($upgrader));
        $this->assertEquals(true, $check->isError);
    }

    public function testNoWarningWithoutUpgrader(): void
    {
        $check = $this->getMockBuilder(PasswordHashAlgoCheck::class)
            ->setMethodsExcept(['check'])
            ->getMock();

        $check->method('getGlobalConfig')->willReturn([]);
        $check->method('getGlobalVersion')->willReturn('14.0.1');

        $this->assertEquals(true, $check->check(null));
        $this->assertEquals(false, $check->isError);
    }

    public function testWarningWithoutUpgrader(): void
    {
        $check = $this->getMockBuilder(PasswordHashAlgoCheck::class)
            ->setMethodsExcept(['check'])
            ->getMock();

        $check->method('getGlobalConfig')->willReturn([
            'passwordHash' => [
                'backend' => 'sha2',
            ],
        ]);
        $check->method('getGlobalVersion')->willReturn('14.0.1');

        $this->assertEquals(false, $check->check(null));
        $this->assertEquals(false, $check->isError);
    }

    public function testErrorWithoutUpgrader(): void
    {
        $check = $this->getMockBuilder(PasswordHashAlgoCheck::class)
            ->setMethodsExcept(['check'])
            ->getMock();

        $check->method('getGlobalConfig')->willReturn([
            'passwordHash' => [
                'backend' => 'native',
            ],
        ]);
        $check->method('getGlobalVersion')->willReturn('14.1.1');

        $this->assertEquals(false, $check->check(null));
        $this->assertEquals(true, $check->isError);
    }
}
