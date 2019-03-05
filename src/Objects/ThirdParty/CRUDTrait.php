<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
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
    protected $contactLists = array();

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
        // Get Members Core Infos from Api
        $core = API::get(self::getUri($objectId));
        if (null == $core) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Contact (".$objectId.").");
        }
        /** @codingStandardsIgnoreStart */
        $mjObject = $core->Data[0];

        //====================================================================//
        // Get Members Attached Lists from Api
        $lists = API::get(self::getListUri($objectId));
        if (null == $lists) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Contact Lists Infos (".$objectId.").");
        }
        $this->contactLists = $lists->Data;
        //====================================================================//
        // Check Contact is In Current List
        if (!$this->isInCurrentList()) {
            return false;
        }

        //====================================================================//
        // Get Members Properties Infos from Api
        $infos = API::get(self::getDataUri($objectId));
        if (null == $infos) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Contact Properties (".$objectId.").");
        }
        $this->contactData = $infos->Data[0]->Data;
        // @codingStandardsIgnoreEnd

        return $mjObject;
    }

    /**
     * Create Request Object
     *
     * @return false|stdClass New Object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["Email"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Email");
        }
        //====================================================================//
        // Init Object
        $postData = array(
            'Email' => $this->in["Email"],
            'IsExcludedFromCampaigns' => isset($this->in["IsExcludedFromCampaigns"])
                ? $this->in["IsExcludedFromCampaigns"]
                : false,
        );
        //====================================================================//
        // Create New Contact
        $response = API::post(self::getUri(), (object) $postData);
        // @codingStandardsIgnoreStart
        if (is_null($response) || empty($response->Data[0]->ID)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Create Member (".$this->object->Email.").");
        }
        $objectId = $response->Data[0]->ID;
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Add Contact to Current List
        if (!$this->updateListStatus($objectId, 'addnoforce')) {
            return false;
        }

        return $this->load($objectId);
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

        //====================================================================//
        // Update Member Properties
        if ($this->isToUpdate("contactData")) {
            $data = new stdClass();
            // @codingStandardsIgnoreStart
            $data->Data = $this->contactData;
            $response = API::put(self::getDataUri($this->object->ID), $data);
            if (is_null($response) || ($response->Data[0]->ID != $this->object->ID)) {
                // @codingStandardsIgnoreEnd
                return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Member Properties (".$this->object->Email.").");
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
                return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Member (".$this->object->Email.").");
            }
            // @codingStandardsIgnoreEnd
        }

        return $this->getObjectIdentifier();
    }

    /**
     * Delete requested Object
     *
     * @param int $objectId Object Id
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
        return $this->updateListStatus($objectId, 'remove');
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
     * Update MailJet Contact Status in a List
     *
     * @param mixed $objectId
     * @param mixed $status
     *
     * @return bool
     */
    protected function updateListStatus($objectId, $status)
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
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Change Contact Subscription (".$objectId.").");
        }

        return true;
    }

    /**
     * Verify Contact is In Current Selected List
     *
     * @return boolean
     */
    protected function isInCurrentList()
    {
        if (!isset($this->contactLists) || !is_array($this->contactLists)) {
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
     * @param string $objectId
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
     * @param string $objectId
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
