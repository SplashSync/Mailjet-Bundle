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

namespace Splash\Connectors\Mailjet\Objects\ThirdParty;

use Splash\Connectors\Mailjet\Dictionary\ContactListActions;
use Splash\Connectors\Mailjet\Models\Api\Contact;
use Splash\Core\Dictionary\SplFields;
use Splash\Core\Helpers\InlineHelper;
use Splash\OpenApi\Dictionary\ExtendedActionsTypes;
use Splash\OpenApi\Models\Mutation;

/**
 * MailJet ThirdParty Contact Mailing Lists Fields
 */
trait ContactListTrait
{
    /**
     * Build Contact List Fields using FieldFactory
     *
     * @return void
     */
    protected function buildContactListFields(): void
    {
        $listManager = $this->connector->getLocator()->getListsManager();
        //====================================================================//
        // Refresh List of Available Contact Lists
        $listManager->fetchMailingLists();
        //====================================================================//
        // Contact Mailing Lists (Comma-Separated Names)
        $this->fieldsFactory()->create(SplFields::INLINE, "lists")
            ->name("Lists")
            ->description("List to which the contact belongs")
            ->setPreferRead()
            ->addChoices($listManager->getChoices())
        ;
    }

    /**
     * Read Requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getContactListFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Filter on List Field
        if ("lists" !== $fieldName) {
            return;
        }
        //====================================================================//
        // Load Contact Lists via Manager
        $manager = $this->connector->getLocator()->getListsManager();
        $manager->loadContactLists($this->object);
        //====================================================================//
        // Build Inline List Names
        $this->out[$fieldName] = InlineHelper::fromArray($this->object->listIds);
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     *
     * @return void
     */
    protected function setContactListFields(string $fieldName, ?string $fieldData): void
    {
        //====================================================================//
        // Filter on List Field
        if ("lists" !== $fieldName) {
            return;
        }
        $listManager = $this->connector->getLocator()->getListsManager();
        //====================================================================//
        // Ensure Current Lists are Loaded
        $listManager->loadContactLists($this->object);
        //====================================================================//
        // Convert List Names to List IDs
        $newIds = array_filter(array_map(
            fn ($listName) => $listManager->getIndex($listName),
            InlineHelper::toArray($fieldData)
        ));
        $oldIds = array_keys($this->object->listIds);
        //====================================================================//
        // Compute Lists Changes
        $toAdd = array_values(array_diff($newIds, $oldIds));
        $toRemove = array_values(array_diff($oldIds, $newIds));
        //====================================================================//
        // Apply Lists Changes via Mutation
        if (!empty($toAdd) || !empty($toRemove)) {
            $this->visitor->getExtendedActionsBuffer()->add(
                ExtendedActionsTypes::POST_UPDATE,
                $this->buildContactListMutation($toAdd, $toRemove)
            );
            $this->needUpdate();
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Build a Mutation for Contact Lists Update
     *
     * @param int[] $toAdd    List IDs to subscribe
     * @param int[] $toRemove List IDs to unsubscribe
     */
    private function buildContactListMutation(array $toAdd, array $toRemove): Mutation
    {
        //====================================================================//
        // Build ContactsLists Payload
        $contactsLists = array();
        foreach ($toAdd as $listId) {
            $contactsLists[] = array(
                "ListID" => $listId,
                "Action" => ContactListActions::ADD_NO_FORCE,
            );
        }
        foreach ($toRemove as $listId) {
            $contactsLists[] = array(
                "ListID" => $listId,
                "Action" => ContactListActions::REMOVE,
            );
        }

        return Mutation::create(
            Contact::class,
            sprintf("/contact/%s/managecontactslists", $this->object->getId()),
        )->withData(array("ContactsLists" => $contactsLists));
    }
}
