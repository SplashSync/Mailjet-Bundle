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

namespace Splash\Connectors\Mailjet\Models\Connector;

use Httpful\Request;
use Splash\Connectors\Mailjet\Dictionary\MailjetEndpoints;
use Splash\Connectors\Mailjet\Models\Api\Action as MailjetAction;
use Splash\Connectors\Mailjet\Services\Connexion\MailjetErrorParser;
use Splash\Connectors\Mailjet\Services\MailjetLocator;
use Splash\OpenApi\Connexion\JsonConnexion;
use Splash\OpenApi\Hydrators\SymfonyHydrator;
use Splash\OpenApi\Interfaces\ConnexionInterface;
use Splash\OpenApi\Visitor\JsonVisitor;
use Webmozart\Assert\Assert;

/**
 * Manage Mailjet Connector APi Configuration
 */

trait MailjetApiTrait
{
    /**
     * @var array<string, ConnexionInterface>
     */
    private array $connexions = array();

    /**
     * Get Connector Api Connexion
     */
    public function getConnexion(): ConnexionInterface
    {
        $wsId = $this->getWebserviceId();
        //====================================================================//
        // Connexion already created
        if (isset($this->connexions[$wsId])) {
            return $this->connexions[$wsId];
        }
        //====================================================================//
        // Safety check
        Assert::true($this->selfTest(), "Self-test fails... Unable to create API Connexion!");
        //====================================================================//
        // Fetch Connector Configuration
        $config = $this->getConfiguration();
        //====================================================================//
        // Setup Api Connexion
        $connexion = new JsonConnexion(
            MailjetEndpoints::getEndpoint($this->isSandbox()),
            array(),
            function (Request $request) use ($config) {
                $request
                    ->authenticateWith($config["ApiKey"], $config["SecretKey"])
                    ->sendsJson()
                    ->expectsJson()
                    ->timeout(3)
                ;
            }
        );
        //====================================================================//
        // Setup Rate Limiter
        //        $connexion->setRateLimiter($this->getLocator()->getRateLimiter());
        //====================================================================//
        // Setup Error Parser
        $connexion->setErrorParser(new MailjetErrorParser());

        return $this->connexions[$wsId] = $connexion;
    }

    /**
     * Get Connector Hydrator
     */
    public function getHydrator(): SymfonyHydrator
    {
        return $this->hydrator;
    }

    /**
     * Get Mailjet Connector Services Locator
     */
    public function getLocator(): MailjetLocator
    {
        return $this->locator->configure($this);
    }

    /**
     * Get Mailjet API Visitor configured with Mailjet-specific actions.
     *
     * @param class-string $model API model class
     */
    public function getVisitor(string $model): JsonVisitor
    {
        $visitor = new JsonVisitor(
            $this->getRestAdapter(),
            $this->getConnexion(),
            $this->getHydrator(),
            $model,
        );
        //====================================================================//
        // Configure Mailjet-specific actions (unwrap Data[] response format)
        $visitor->setTimezone("UTC");
        $visitor->setLoadAction(MailjetAction\GetAction::class);
        $visitor->setCreateAction(MailjetAction\PostAction::class);
        $visitor->setUpdateAction(MailjetAction\PutAction::class);
        $visitor->setListAction(MailjetAction\ListAction::class);

        return $visitor;
    }

    /**
     * Check if we are in Sandbox Mode
     */
    public function isSandbox(): bool
    {
        return !empty($this->getParameter("isSandbox", false));
    }
}
