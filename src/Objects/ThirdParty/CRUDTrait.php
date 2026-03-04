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

use Splash\Connectors\Mailjet\Models\Api\Contact;
use Splash\Core\Client\Splash;
use Splash\Core\Helpers\InlineHelper;

/**
 * Mailjet Users CRUD Functions
 */
trait CRUDTrait
{
    /**
     * {@inheritDoc}
     */
    public function getByPrimary(array $keys): ?string
    {
        //====================================================================//
        // Safety Check
        $email = $keys['Email'] ?? null;
        if (!$email) {
            return null;
        }
        //====================================================================//
        // Try to Load Contact by Email (Mailjet API accepts email as ID)
        $contact = $this->load(urlencode((string) $email));
        //====================================================================//
        // Clean Splash Log
        Splash::log()->cleanLog();

        return $contact ? $contact->getId() : null;
    }

    /**
     * Create Request Object
     */
    public function create(): ?Contact
    {
        //====================================================================//
        // Add Contact to Default List (only if no lists provided)
        if (empty($this->in["lists"] ?? null)) {
            if ($listName = $this->connector->getLocator()->getListsManager()->getDefaultListName()) {
                $this->in["lists"] = InlineHelper::fromArray(array($listName));
                $this->needUpdate();
            }
        }
        //====================================================================//
        // Execute Core Create
        $contact = $this->coreCreate();
        if (!$contact instanceof Contact) {
            return null;
        }

        return $contact;
    }
}
