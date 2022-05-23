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

namespace Splash\Connectors\Mailjet\Objects\WebHook;

use Splash\Connectors\Mailjet\Models\MailjetHelper as API;
use Splash\Core\SplashCore      as Splash;
use stdClass;

/**
 * Mailjet WebHook CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object ID
     *
     * @return null|stdClass
     */
    public function load(string$objectId): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Execute Read Request
        $mjWebHook = API::get(self::getUri($objectId));
        //====================================================================//
        // Fetch Object
        if (null == $mjWebHook) {
            return Splash::log()->errNull("Unable to load WebHook (".$objectId.").");
        }
        // @codingStandardsIgnoreStart
        return $mjWebHook->Data[0];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Create Request Object
     *
     * @param null|string $url
     *
     * @return null|stdClass New Object
     */
    public function create(string $url = null): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($url) && empty($this->in["Url"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Url");

            return null;
        }
        //====================================================================//
        // Init Object
        $this->object = new stdClass();
        //====================================================================//
        // Pre-Setup of Member
        $this->setSimple("Url", empty($url) ? $this->in["Url"] : $url);
        $this->setSimple("EventType", "unsub");
        $this->setSimple("IsBackup", true);
        $this->setSimple("Status", "alive");
        //====================================================================//
        // Create Object
        $response = API::post(self::getUri(), $this->object);
        // @codingStandardsIgnoreStart
        if (is_null($response) || !($response->Data[0] instanceof stdClass) || empty($response->Data[0]->ID)) {
            return Splash::log()->errNull("Unable to Create WebHook");
        }

        return $response->Data[0];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID of False if Failed to Update
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (!$needed) {
            return (string) $this->object->ID;
        }

        //====================================================================//
        // Update Not Allowed
        Splash::log()->errTrace("WebHook Update is disabled.");

        return $this->getObjectIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $response = API::delete(self::getUri($objectId));
        if (null === $response) {
            return Splash::log()->errTrace("Unable to Delete Member (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (!isset($this->object->ID)) {
            return null;
        }

        return (string) $this->object->ID;
    }

    /**
     * Get Object CRUD Uri
     *
     * @param null|string $objectId
     *
     * @return string
     */
    private static function getUri(string $objectId = null) : string
    {
        $baseUri = 'eventcallbackurl';
        if (!is_null($objectId)) {
            return $baseUri."/".$objectId;
        }

        return $baseUri;
    }
}
