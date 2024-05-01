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

namespace Sugarcrm\SugarcrmTestsUnit;

use Closure;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\PubSub\Client\Log\PushClient as PubSubPushClient;
use Sugarcrm\Sugarcrm\PubSub\Client\PushClientInterface as PubSubPushClientInterface;

/**
 * Class TestDependencyInjectionHelper
 *
 * Wrapper around the Dependency Injection container.
 */
class TestDependencyInjectionHelper
{
    /**
     * Restores the default DI container and adds common overrides.
     *
     * @return void
     */
    public static function resetContainer(): void
    {
        Container::resetInstance();

        // Don't allow Pub/Sub events to be sent.
        static::setService(
            PubSubPushClientInterface::class,
            function (ContainerInterface $container): PubSubPushClientInterface {
                $logger = $container->get(LoggerInterface::class);

                return new PubSubPushClient($logger);
            }
        );
    }

    /**
     * Adds a service to the DI container.
     *
     * @param string $serviceId
     * @param Closure $serviceFactory
     *
     * @return void
     */
    public static function setService(string $serviceId, Closure $serviceFactory): void
    {
        $container = Container::getInstance();
        $container->set($serviceId, $serviceFactory);
    }
}
