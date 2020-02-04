<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
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
     * @param string $objectId Object id
     *
     * @return mixed
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Execute Read Request
        $mjWebHook = API::get(self::getUri($objectId));
        //====================================================================//
        // Fatch Object
        if (null == $mjWebHook) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load WebHook (".$objectId.").");
        }
        // @codingStandardsIgnoreStart
        return $mjWebHook->Data[0];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Create Request Object
     *
     * @param string $url
     *
     * @return false|stdClass New Object
     */
    public function create(string $url = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($url) && empty($this->in["Url"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Url");
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
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Create WebHook");
        }

        return $response->Data[0];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id of False if Failed to Update
     */
    public function update(bool $needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (!$needed) {
            return (string) $this->object->ID;
        }

        //====================================================================//
        // Update Not Allowed
        Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " WebHook Update is diasbled.");

        return $this->getObjectIdentifier();
    }

    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $response = API::delete(self::getUri($objectId));
        if (null === $response) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Delete Member (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->ID)) {
            return false;
        }

        return (string) $this->object->ID;
    }

    /**
     * Get Object CRUD Uri
     *
     * @param string $objectId
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
