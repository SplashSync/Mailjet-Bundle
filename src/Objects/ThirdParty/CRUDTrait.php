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

use Splash\Connectors\Mailjet\Models\MailjetHelper as API;
use Splash\Core\SplashCore      as Splash;
use stdClass;

/**
 * Mailjet Users CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Storage for Members Lists Subscriptions
     *
     * @var array
     */
    protected array $contactLists = array();

    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return null|stdClass
     */
    public function load(string $objectId): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Get Members Core Infos from Api
        $core = API::get(self::getUri($objectId));
        if (null == $core) {
            return Splash::log()->errNull("Unable to load Contact (".$objectId.").");
        }
        /** @codingStandardsIgnoreStart */
        $mjObject = $core->Data[0];

        //====================================================================//
        // Get Members Attached Lists from Api
        $lists = API::get(self::getListUri($objectId));
        if (null == $lists) {
            return Splash::log()->errNull("Unable to load Contact Lists Infos (".$objectId.").");
        }
        $this->contactLists = $lists->Data;
        //====================================================================//
        // Check Contact is In Current List
        if (!$this->isInCurrentList()) {
            return null;
        }

        //====================================================================//
        // Get Members Properties Infos from Api
        $infos = API::get(self::getDataUri($objectId));
        if (null == $infos) {
            return Splash::log()->errNull("Unable to load Contact Properties (".$objectId.").");
        }
        $this->contactData = $infos->Data[0]->Data;
        // @codingStandardsIgnoreEnd

        return $mjObject;
    }

    /**
     * Create Request Object
     *
     * @return null|stdClass New Object
     */
    public function create(): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["Email"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Email");

            return null;
        }
        //====================================================================//
        // Init Object
        $postData = array(
            'Email' => $this->in["Email"],
            'IsExcludedFromCampaigns' => $this->in["IsExcludedFromCampaigns"] ?? false,
        );
        //====================================================================//
        // Create New Contact
        $response = API::post(self::getUri(), (object) $postData);
        // @codingStandardsIgnoreStart
        if (is_null($response) || empty($response->Data[0]->ID)) {
            return Splash::log()->errNull("Unable to Create Member (".$this->object->Email.").");
        }
        $objectId = $response->Data[0]->ID;
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Add Contact to Current List
        if (!$this->updateListStatus($objectId, 'addnoforce')) {
            return null;
        }

        return $this->load($objectId);
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID of NULL if Failed to Update
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Update Member Properties
        if ($this->isToUpdate("contactData")) {
            $data = new stdClass();
            // @codingStandardsIgnoreStart
            $data->Data = $this->contactData;
            $response = API::put(self::getDataUri($this->object->ID), $data);
            if (is_null($response) || ($response->Data[0]->ID != $this->object->ID)) {
                // @codingStandardsIgnoreEnd
                return Splash::log()->errNull("Unable to Update Member Properties (".$this->object->Email.").");
            }
        }

        //====================================================================//
        // Update Member Core Data
        if ($needed) {
            //====================================================================//
            // Extract Only Needed Data
            $data = (array) $this->object;
            unset($data['LastActivityAt']);
            //====================================================================//
            // Update Object
            $response = API::put(self::getUri($this->object->ID), (object) $data);
            // @codingStandardsIgnoreStart
            if (is_null($response) || ($response->Data[0]->ID != $this->object->ID)) {
                return Splash::log()->errNull("Unable to Update Member (".$this->object->Email.").");
            }
            // @codingStandardsIgnoreEnd
        }

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
        return $this->updateListStatus($objectId, 'remove');
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
     * Update MailJet Contact Status in a List
     *
     * @param string $objectId
     * @param string $status
     *
     * @return bool
     */
    protected function updateListStatus(string $objectId, string $status): bool
    {
        if (!in_array($status, array('unsub', 'addforce', 'addnoforce', 'remove'), true)) {
            return false;
        }
        //====================================================================//
        // Prepare Parameters
        $body = new stdClass();
        // @codingStandardsIgnoreStart
        $body->ContactsLists = array( (object) array(
            // @codingStandardsIgnoreEnd
            'ListID' => API::getList(),
            'Action' => $status,
        ), );

        //====================================================================//
        // Update Object
        $response = API::post(self::getSubscribeUri($objectId), $body);
        if (null === $response) {
            return Splash::log()->errTrace("Unable to Change Contact Subscription (".$objectId.").");
        }

        return true;
    }

    /**
     * Verify Contact is In Current Selected List
     *
     * @return bool
     */
    protected function isInCurrentList(): bool
    {
        if (!isset($this->contactLists)) {
            return false;
        }
        foreach ($this->contactLists as $list) {
            // @codingStandardsIgnoreStart
            if ($list->ListID != API::getList()) {
                continue;
            }
            // @codingStandardsIgnoreEnd

            return true;
        }

        return false;
    }

    /**
     * Get Object CRUD Base Uri
     *
     * @param null|string $objectId
     *
     * @return string
     */
    private static function getUri(string $objectId = null) : string
    {
        $baseUri = 'contact';
        if (!is_null($objectId)) {
            return $baseUri."/".$objectId;
        }

        return $baseUri;
    }

    /**
     * Get Object CRUD List Uri
     *
     * @param string $objectId
     *
     * @return string
     */
    private static function getListUri(string $objectId) : string
    {
        return 'contact/'.$objectId."/getcontactslists";
    }

    /**
     * Get Object CRUD Properties Uri
     *
     * @param null|string $objectId
     *
     * @return string
     */
    private static function getDataUri(string $objectId = null) : string
    {
        $baseUri = 'contactdata';
        if (!is_null($objectId)) {
            return $baseUri."/".$objectId;
        }

        return $baseUri;
    }

    /**
     * Get Object CRUD List Subscribe Uri
     *
     * @param string $objectId
     *
     * @return string
     */
    private static function getSubscribeUri(string $objectId) : string
    {
        return 'contact/'.$objectId."/managecontactslists";
    }
}
