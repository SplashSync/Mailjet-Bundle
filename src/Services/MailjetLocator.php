<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Mailjet\Services;

use Psr\Container\ContainerInterface;
use Splash\Connectors\Mailjet\Models\MailjetConnectorAwareTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Webmozart\Assert\Assert;

class MailjetLocator implements ServiceSubscriberInterface
{
    use MailjetConnectorAwareTrait;

    public function __construct(
        private ContainerInterface $locator,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return array(
            Managers\ListsManager::class,
            Managers\PropertiesManager::class,
            Managers\WebHookManager::class,
            Connexion\MailjetRateLimiter::class,
        );
    }

    //====================================================================//
    // Access to Configured Services
    //====================================================================//

    /**
     * Get Mailjet Lists Manager
     */
    public function getListsManager(): Managers\ListsManager
    {
        Assert::isInstanceOf(
            $service = $this->locator->get(Managers\ListsManager::class),
            Managers\ListsManager::class
        );

        return $service->configure($this->connector);
    }

    /**
     * Get Mailjet Properties Manager
     */
    public function getPropertiesManager(): Managers\PropertiesManager
    {
        Assert::isInstanceOf(
            $service = $this->locator->get(Managers\PropertiesManager::class),
            Managers\PropertiesManager::class
        );

        return $service->configure($this->connector);
    }

    /**
     * Get Mailjet WebHook Manager
     */
    public function getWebHookManager(): Managers\WebHookManager
    {
        Assert::isInstanceOf(
            $service = $this->locator->get(Managers\WebHookManager::class),
            Managers\WebHookManager::class
        );

        return $service->configure($this->connector);
    }

    /**
     * Get Mailjet Rate Limiter
     */
    public function getRateLimiter(): Connexion\MailjetRateLimiter
    {
        Assert::isInstanceOf(
            $service = $this->locator->get(Connexion\MailjetRateLimiter::class),
            Connexion\MailjetRateLimiter::class
        );

        return $service;
    }
}
