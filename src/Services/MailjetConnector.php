<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Mailjet\Services;

use ArrayObject;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
use Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;
use Splash\Connectors\Mailjet\Form\EditFormType;
use Splash\Connectors\Mailjet\Form\NewFormType;
use Splash\Connectors\Mailjet\Models\MailjetHelper as API;
use Splash\Connectors\Mailjet\Objects\WebHook;
use Splash\Core\SplashCore as Splash;
use Symfony\Component\Routing\RouterInterface;

/**
 * Mailjet REST API Connector for Splash
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MailjetConnector extends AbstractConnector
{
    use GenericObjectMapperTrait;
    use GenericWidgetMapperTrait;

    /**
     * Objects Type Class Map
     *
     * @var array
     */
    protected static $objectsMap = array(
        "ThirdParty" => "Splash\\Connectors\\Mailjet\\Objects\\ThirdParty",
    );

    /**
     * Widgets Type Class Map
     *
     * @var array
     */
    protected static $widgetsMap = array(
        "SelfTest" => "Splash\\Connectors\\Mailjet\\Widgets\\SelfTest",
    );

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
        return API::ping();
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
        if (!API::connect()) {
            return false;
        }
        //====================================================================//
        // Get List of Available Lists
        if (!$this->fetchMailingLists()) {
            return false;
        }
        //====================================================================//
        // Get List of Available Members Properties
        if (!$this->fetchPropertiesLists()) {
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
        $informations->moduleauthor = SPLASH_AUTHOR;
        $informations->moduleversion = "master";

        //====================================================================//
        // Load API Configurations
        $config = $this->getConfiguration();
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest() || empty($config["ApiList"])) {
            return $informations;
        }
        //====================================================================//
        // Get List Detailed Informations
        $details = API::get('myprofile');
        if (is_null($details)) {
            return $informations;
        }

        //====================================================================//
        // Company Informations
        // @codingStandardsIgnoreStart
        $informations->company = $details->Data[0]->CompanyName;
        $informations->address = $details->Data[0]->AddressStreet;
        $informations->zip = $details->Data[0]->AddressPostalCode;
        $informations->town = $details->Data[0]->AddressCity;
        $informations->country = $details->Data[0]->AddressCountry;
        $informations->www = $details->Data[0]->Website;
        $informations->email = " ";
        $informations->phone = $details->Data[0]->ContactPhone;
        // @codingStandardsIgnoreEnd

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
        if (!isset($config["ApiKey"]) || empty($config["ApiKey"]) || !is_string($config["ApiKey"])) {
            Splash::log()->err("Api Key is Invalid");

            return false;
        }

        //====================================================================//
        // Verify Secret Key is Set
        //====================================================================//
        if (!isset($config["SecretKey"]) || empty($config["SecretKey"]) || !is_string($config["SecretKey"])) {
            Splash::log()->err("Secret Key is Invalid");

            return false;
        }

        //====================================================================//
        // Configure Rest API
        return API::configure(
            $config["ApiKey"],
            $config["SecretKey"],
            isset($config["ApiList"]) ? $config["ApiList"] : null
        );
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
    public function getFile(string $filePath, string $fileMd5)
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        Splash::log()->err("There are No Files Reading for Mailjet Up To Now!");

        return false;
    }

    //====================================================================//
    // Profile Interfaces
    //====================================================================//

    /**
     * @abstract   Get Connector Profile Informations
     *
     * @return array
     */
    public function getProfile() : array
    {
        return array(
            'enabled' => true,                                  // is Connector Enabled
            'beta' => false,                                    // is this a Beta release
            'type' => self::TYPE_ACCOUNT,                       // Connector Type or Mode
            'name' => 'mailjet',                                // Connector code (lowercase, no space allowed)
            'connector' => 'splash.connectors.mailjet',         // Connector Symfony Service
            'title' => 'profile.card.title',                    // Public short name
            'label' => 'profile.card.label',                    // Public long name
            'domain' => 'MailjetBundle',                        // Translation domain for names
            'ico' => '/bundles/mailjet/img/MailJet-Icon.png',   // Public Icon path
            'www' => 'www.mailjet.com',                         // Website Url
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectedTemplate() : string
    {
        return "@Mailjet/Profile/connected.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineTemplate() : string
    {
        return "@Mailjet/Profile/offline.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getNewTemplate() : string
    {
        return "@Mailjet/Profile/new.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilderName() : string
    {
        return $this->getParameter("ApiListsIndex", false) ? EditFormType::class : NewFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterAction()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicActions() : array
    {
        return array(
            "index" => "MailjetBundle:WebHooks:index",
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredActions() : array
    {
        return array(
            "webhooks" => "MailjetBundle:Actions:webhooks",
        );
    }

    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//

    /**
     * Check & Update Mailjet Api Account WebHooks.
     *
     * @return bool
     */
    public function verifyWebHooks() : bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Generate WebHook Url
        $webHookServer = filter_input(INPUT_SERVER, 'SERVER_NAME');
        //====================================================================//
        // When Running on a Local Server
        if (false !== strpos("localhost", $webHookServer)) {
            $webHookServer = "www.splashsync.com";
        }
        //====================================================================//
        // Create Object Class
        $webHookManager = new WebHook($this);
        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks = $webHookManager->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Filter & Clean List Of WebHooks
        foreach ($webHooks as $webHook) {
            //====================================================================//
            // This is a Splash WebHooks
            if (false !== strpos(trim($webHook['Url']), $webHookServer)) {
                return true;
            }
        }
        //====================================================================//
        // Splash WebHooks was NOT Found
        return false;
    }

    /**
     * Check & Update Mailjet Api Account WebHooks.
     *
     * @param RouterInterface $router
     *
     * @return bool
     */
    public function updateWebHooks(RouterInterface $router) : bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Generate WebHook Url
        $webHookServer = filter_input(INPUT_SERVER, 'SERVER_NAME');
        $webHookUrl = $router->generate(
            'splash_connector_action',
            array(
                'connectorName' => $this->getProfile()["name"],
                'webserviceId' => $this->getWebserviceId(),
            ),
            RouterInterface::ABSOLUTE_URL
        );
        //====================================================================//
        // When Running on a Local Server
        if (false !== strpos("localhost", $webHookServer)) {
            $webHookServer = "www.splashsync.com";
            $webHookUrl = "https://www.splashsync.com/en/ws/mailjet/123456";
        }
        //====================================================================//
        // Create Object Class
        $webHookManager = new WebHook($this);
        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks = $webHookManager->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Filter & Clean List Of WebHooks
        $foundWebHook = false;
        foreach ($webHooks as $webHook) {
            //====================================================================//
            // This is Current Node WebHooks
            if (trim($webHook['Url']) == $webHookUrl) {
                $foundWebHook = true;

                continue;
            }
            //====================================================================//
            // This is a Splash WebHooks
            if (false !== strpos(trim($webHook['Url']), $webHookServer)) {
                $webHookManager->delete($webHook['id']);
            }
        }
        //====================================================================//
        // Splash WebHooks was Found
        if ($foundWebHook) {
            return true;
        }
        //====================================================================//
        // Add Splash WebHooks
        return (false !== $webHookManager->create($webHookUrl));
    }

    //====================================================================//
    //  LOW LEVEL PRIVATE FUNCTIONS
    //====================================================================//

    /**
     * Get Mailjet User Lists
     *
     * @return bool
     */
    private function fetchMailingLists()
    {
        //====================================================================//
        // Get User Lists from Api
        $response = API::get('contactslist');
        if (is_null($response)) {
            return false;
        }
        // @codingStandardsIgnoreStart
        if (!isset($response->Data)) {
            return false;
        }
        //====================================================================//
        // Parse Lists to Connector Settings
        $listIndex = array();
        foreach ($response->Data as $listDetails) {
            //====================================================================//
            // Add List Index
            $listIndex[$listDetails->ID] = $listDetails->Name;
        }
        //====================================================================//
        // Store in Connector Settings
        $this->setParameter("ApiListsIndex", $listIndex);
        $this->setParameter("ApiListsDetails", $response->Data);
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Update Connector Settings
        $this->updateConfiguration();

        return true;
    }

    /**
     * Get Mailjet User Properties Lists
     *
     * @return bool
     */
    private function fetchPropertiesLists()
    {
        //====================================================================//
        // Get User Lists from Api
        $response = API::get('contactmetadata');
        if (is_null($response)) {
            return false;
        }
        // @codingStandardsIgnoreStart
        if (!isset($response->Data)) {
            return false;
        }
        //====================================================================//
        // Store in Connector Settings
        $this->setParameter("MembersAttributes", $response->Data);
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Update Connector Settings
        $this->updateConfiguration();

        return true;
    }
}
