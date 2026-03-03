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

namespace Splash\Connectors\Mailjet\Services\Managers;

use Splash\Connectors\Mailjet\Models\MailjetConnectorAwareTrait;
use Webmozart\Assert\Assert;

/**
 * Manage Mailjet Contacts Lists
 */
class ListsManager
{
    use MailjetConnectorAwareTrait;

    /**
     * Default API List Index
     */
    const string DEFAULT_INDEX = "ApiList";

    /**
     * API List Indexes Storage Key
     */
    const string LISTS_INDEX = "ApiListsIndex";

    /**
     * API List Details Storage Key
     */
    const string LISTS_DETAILS = "ApiListsDetails";

    /**
     * Get Mailjet User Lists
     *
     * @return bool
     */
    public function fetchMailingLists(): bool
    {
        //====================================================================//
        // Get User Lists from Api
        $response = $this->getConnexion()->get('/contactslist');
        if (is_null($response) || empty($response["Data"]) || !is_array($response["Data"])) {
            return false;
        }
        //====================================================================//
        // Parse Lists to Connector Settings
        $listIndex = array();
        foreach ($response["Data"] as $listDetails) {
            Assert::isArray($listDetails);
            //====================================================================//
            // Add List Index
            $listIndex[$listDetails["ID"]] = $listDetails["Name"];
        }
        //====================================================================//
        // Store in Connector Settings
        $this->getConnector()->setParameter(self::LISTS_INDEX, $listIndex);
        $this->getConnector()->setParameter(self::LISTS_DETAILS, $response["Data"]);
        //====================================================================//
        // Update Connector Settings
        $this->getConnector()->updateConfiguration();

        return true;
    }

    /**
     * Get Default Contact List Name
     */
    public function getDefaultListName(): ?string
    {
        $index = $this->getConnector()->getParameter(self::DEFAULT_INDEX);
        if (!is_numeric($index)) {
            return null;
        }

        return $this->getName((int) $index);
    }

    /**
     * Get List Name from ID
     */
    public function getName(int $listId): ?string
    {
        $index = $this->getConnector()->getParameter(self::LISTS_INDEX);
        if (!is_array($index) || !isset($index[$listId])) {
            return null;
        }

        return is_scalar($index[$listId]) ? (string) $index[$listId] : null;
    }

    /**
     * Get List ID from Name
     */
    public function getIndex(string $listName): ?int
    {
        $index = $this->getConnector()->getParameter(self::LISTS_INDEX);
        if (!is_array($index)) {
            return null;
        }
        //====================================================================//
        // Search by Name
        $listId = array_search($listName, $index, true);

        return (false !== $listId) ? (int) $listId : null;
    }

    /**
     * Get All Lists as Choices Array
     *
     * @return array<string, string>
     */
    public function getChoices(): array
    {
        $index = $this->getConnector()->getParameter(self::LISTS_INDEX);
        if (!is_array($index)) {
            return array();
        }

        /** @var  string[] $index */
        return array_combine(array_values($index), array_values($index));
    }
}