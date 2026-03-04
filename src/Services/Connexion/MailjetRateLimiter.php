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

namespace Splash\Connectors\Mailjet\Services\Connexion;

use Httpful\Response;
use Splash\OpenApi\Interfaces\RateLimiterInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Mailjet API Rate Limiter
 */
class MailjetRateLimiter implements RateLimiterInterface
{
    /**
     * Symfony Rate Limiter Configuration Key
     */
    const string CONFIG_KEY = 'mailjet_api';

    /**
     * Limiter key (typically the connector WebService ID)
     */
    private string $key = 'default';

    public function __construct(
        #[Target(self::CONFIG_KEY)]
        private readonly RateLimiterFactoryInterface $rateLimiterFactory,
    ) {
    }

    /**
     * Set the limiter key (connector WebService ID)
     */
    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function waitIfNeeded(): void
    {
        //====================================================================//
        // Consume one token, wait if limit is reached
        $this->rateLimiterFactory->create($this->key)->reserve()->wait();
    }

    /**
     * {@inheritDoc}
     */
    public function updateFromResponse(Response $response): bool
    {
        //====================================================================//
        // Retry on 429 Too Many Requests
        return 429 === $response->code;
    }
}
