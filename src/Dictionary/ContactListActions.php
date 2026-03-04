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

namespace Splash\Connectors\Mailjet\Dictionary;

/**
 * Mailjet Contact List Management Actions
 *
 * Used with POST /contact/{id}/managecontactslists endpoint
 */
class ContactListActions
{
    /**
     * Add contact to list, even if previously unsubscribed
     */
    const string ADD_FORCE = "addforce";

    /**
     * Add contact to list only if not already present
     */
    const string ADD_NO_FORCE = "addnoforce";

    /**
     * Unsubscribe contact from list (contact remains in list but marked as unsubscribed)
     */
    const string UNSUB = "unsub";

    /**
     * Remove contact from list entirely
     */
    const string REMOVE = "remove";

    /**
     * All available actions with descriptions
     *
     * @var array<string, string>
     */
    const array ALL = array(
        self::ADD_FORCE => "Force add contact to list",
        self::ADD_NO_FORCE => "Add contact to list if not present",
        self::UNSUB => "Unsubscribe contact from list",
        self::REMOVE => "Remove contact from list",
    );
}
