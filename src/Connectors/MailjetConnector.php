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

namespace Splash\Connectors\Mailjet\Connectors;

use ArrayObject;
use Psr\Log\LoggerInterface;
use Splash\Bundle\Interfaces\ConnectorInterface;
use Splash\Bundle\Interfaces\Connectors\PrimaryKeysInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
use Splash\Bundle\Models\Connectors\GenericObjectPrimaryMapperTrait;
use Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;
use Splash\Bundle\Models\Connectors\RoutesBuilderAwareTrait;
use Splash\Bundle\Services\ConnectorRoutesBuilder;
use Splash\Connectors\Mailjet\Models\Connector\MailjetApiTrait;
use Splash\Connectors\Mailjet\Models\Connector\MailjetProfileTrait;
use Splash\Connectors\Mailjet\Models\MailjetHelper as API;
use Splash\Connectors\Mailjet\Objects;
use Splash\Connectors\Mailjet\Services\MailjetLocator;
use Splash\Core\Client\Splash;
use Splash\Core\Dictionary\SplDefinition;
use Splash\OpenApi\Hydrators\SymfonyHydrator;
use Splash\OpenApi\Models\Connector\RestAdapterAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Mailjet REST API Connector for Splash
 */
#[AutoconfigureTag(ConnectorInterface::TAG)]
class MailjetConnector extends AbstractConnector implements PrimaryKeysInterface
{
    use RestAdapterAwareTrait;
    use MailjetApiTrait;
    use MailjetProfileTrait;
    use GenericObjectMapperTrait;
    use GenericObjectPrimaryMapperTrait;
    use GenericWidgetMapperTrait;
    use RoutesBuilderAwareTrait;

    /**
     * Objects Type Class Map
     *
     * @var array<string, class-string>
     */
    protected static array $objectsMap = array(
        "ThirdParty" => Objects\ThirdParty::class,
        "Webhook" => Objects\Webhook::class,
    );

    /**
     * Widgets Type Class Map
     *
     * @var array<string, class-string>
     */
    protected static array $widgetsMap = array(
        "SelfTest" => "Splash\\Connectors\\Mailjet\\Widgets\\SelfTest",
    );

    /**
     * Class Constructor
     */
    public function __construct(
        private readonly SymfonyHydrator   $hydrator,
        private readonly MailjetLocator     $locator,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        ConnectorRoutesBuilder $routesBuilder,
    ) {
        parent::__construct($eventDispatcher, $logger);
        $this->setRouteBuilder($routesBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function ping() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Ping Test
        $this->getConnexion()->get("/myprofile");
        //====================================================================//
        // Check Response
        $response = $this->getConnexion()->getLastResponse();
        if ($response && ($response->code >= 200) && ($response->code < 500)) {
            return true;
        }

        //====================================================================//
        // Ping Test Fail
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function connect() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Connect Test
        $this->getConnexion()->get("/myprofile");
        //====================================================================//
        // Check Response
        $response = $this->getConnexion()->getLastResponse();
        if (!$response || (200 != $response->code)) {
            return false;
        }
        //====================================================================//
        // Get List of Available Lists
        if (!$this->getLocator()->getListsManager()->fetchMailingLists()) {
            return false;
        }

        //====================================================================//
        // Get List of Available Members Properties
        if (!$this->getLocator()->getPropertiesManager()->fetchContactProperties()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function informations(ArrayObject  $informations) : ArrayObject
    {
        //====================================================================//
        // Server General Description
        $informations->shortdesc = "Mailjet";
        $informations->longdesc = "Splash Integration for Mailjet's Api V3.0";
        //====================================================================//
        // Server Logo & Ico
        $informations->icoraw = Splash::file()->readFileContents(
            dirname(dirname(__FILE__))."/Resources/public/img/MailJet-Icon.png"
        );
        $informations->logourl = null;
        $informations->logoraw = Splash::file()->readFileContents(
            dirname(dirname(__FILE__))."/Resources/public/img/MailJet-Logo.jpg"
        );
        //====================================================================//
        // Server Informations
        $informations->servertype = "Mailjet REST Api V3";
        $informations->serverurl = API::ENDPOINT;
        //====================================================================//
        // Module Informations
        $informations->moduleauthor = "Splash Sync";
        $informations->moduleversion = SplDefinition::VERSION;

        //====================================================================//
        // Load API Configurations
        $config = $this->getConfiguration();
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest() || empty($config["ApiList"])) {
            return $informations;
        }
        //====================================================================//
        // Get List Detailed Information
        //====================================================================//
        // Perform Connect Test
        $response = $this->getConnexion()->get("/myprofile");
        if (is_null($response) || !is_array($profile = $response["Data"][0] ?? null)) {
            return $informations;
        }

        //====================================================================//
        // Company Information
        $informations->company = $profile["CompanyName"] ?? null;
        $informations->address = $profile["AddressStreet"] ?? null;
        $informations->zip = $profile["AddressPostalCode"] ?? null;
        $informations->town = $profile["AddressCity"] ?? null;
        $informations->country = $profile["AddressCountry"] ?? null;
        $informations->www = $profile["Website"] ?? null;
        $informations->email = " ";
        $informations->phone = $profile["ContactPhone"] ?? null;

        return $informations;
    }

    /**
     * {@inheritdoc}
     */
    public function selfTest() : bool
    {
        $config = $this->getConfiguration();

        //====================================================================//
        // Verify Api Key is Set
        //====================================================================//
        if (empty($config["ApiKey"]) || !is_string($config["ApiKey"])) {
            Splash::log()->err("Api Key is Invalid");

            return false;
        }

        //====================================================================//
        // Verify Secret Key is Set
        //====================================================================//
        if (empty($config["SecretKey"]) || !is_string($config["SecretKey"])) {
            Splash::log()->err("Secret Key is Invalid");

            return false;
        }

        //====================================================================//
        // Sandbox Mode
        //====================================================================//
        if ($this->isSandbox()) {
            Objects\ThirdParty::setSandboxMode();
            Objects\Webhook::setDisabled(false);
        }

        return true;
    }

    //====================================================================//
    // Objects Interfaces
    //====================================================================//

    //====================================================================//
    // Files Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getFile(string $filePath, string $fileMd5): ?array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return null;
        }
        Splash::log()->err("There are No Files Reading for Mailjet Up To Now!");

        return null;
    }

    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//

    /**
     * Check Mailjet Api Account WebHooks.
     */
    public function verifyWebHooks(): bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }

        return $this->getLocator()->getWebHookManager()->verify();
    }

    /**
     * Update Mailjet Api Account WebHooks.
     */
    public function updateWebHooks(): bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }

        return $this->getLocator()->getWebHookManager()->update();
    }
}
