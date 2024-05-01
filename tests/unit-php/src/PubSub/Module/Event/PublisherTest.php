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

namespace Sugarcrm\SugarcrmTestsUnit\PubSub\Module\Event;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\PubSub\Module\Event\Publisher;

class PublisherTest extends TestCase
{
    public function allowedModulesProvider(): array
    {
        return [
            'empty string' => [
                '',
                false,
            ],
            'PubSub_ModuleEvent_PushSubs' => [
                'PubSub_ModuleEvent_PushSubs',
                false,
            ],
            'Contacts' => [
                'Contacts',
                true,
            ],
            'Meetings' => [
                'Meetings',
                true,
            ],
            'Users' => [
                'Users',
                true,
            ],
        ];
    }

    /**
     * @dataProvider allowedModulesProvider
     * @param string $moduleName The module name.
     * @param bool $expected The expected result.
     */
    public function testIsModuleAllowed(string $moduleName, bool $expected): void
    {
        $actual = Publisher::isModuleAllowed($moduleName);

        $this->assertSame($expected, $actual);
    }
}
