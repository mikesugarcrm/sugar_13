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

namespace Sugarcrm\SugarcrmTestsUnit\inc\externalAPI;

use PHPUnit\Framework\TestCase;

/**
 * Class ExternalAPIFactoryTest
 *
 * @coversDefaultClass \ExternalAPIFactory
 */
class ExternalAPIFactoryTest extends TestCase
{
    public function testListApi()
    {
        $loggerReflector = new \ReflectionClass(\LoggerManager::class);
        $instance = $loggerReflector->getProperty('_instance');
        $instance->setAccessible(true);

        $loggerMock = $this->getMockBuilder(\LoggerManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__call'])
            ->getMock();
        $loggerMock->method('__call')->willReturn(true);
        $loggerMock->expects($this->once())->method('__call');

        $instance->setValue($loggerMock);

        $this->assertEmpty(ExternalAPIFactoryNonArrayMock::listAPI('Accounts'));
        $this->assertSame(ExternalAPIFactoryArrayMock::listAPI('Accounts'), [
            'APIName' => [
                'supportedModules' => ['Accounts'],
                'requireAuth' => false,
                'useAuth' => false,
            ],
        ]);

        $instance->setValue(null);
        $instance->setAccessible(false);
    }
}

class ExternalAPIFactoryNonArrayMock extends \ExternalAPIFactory
{
    public static function loadFullAPIList($forceRebuild = false, $ignoreDisabled = false)
    {
        return [
            'APIName' => [
                'supportedModules' => 'Accounts', //incorrect definition
                'requireAuth' => false,
                'useAuth' => false,
            ],
        ];
    }
}

class ExternalAPIFactoryArrayMock extends \ExternalAPIFactory
{
    public static function loadFullAPIList($forceRebuild = false, $ignoreDisabled = false)
    {
        return [
            'APIName' => [
                'supportedModules' => ['Accounts'], //correct definition
                'requireAuth' => false,
                'useAuth' => false,
            ],
        ];
    }
}
