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

namespace Splash\Connectors\Mailjet\Objects\ThirdParty;

use Splash\Connectors\Mailjet\Models\MailjetHelper as API;

/**
 * MailJet ThirdParty Core Fields (Required)
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    protected function buildCoreFields()
    {
        //====================================================================//
        // Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->Identifier("Email")
            ->Name("Email")
            ->MicroData("http://schema.org/ContactPoint", "email")
            ->isRequired()
            ->isListed();

        //====================================================================//
        // Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Name")
            ->Name("Username")
            ->MicroData("http://schema.org/Organization", "legalName");

        //====================================================================//
        // Subscribed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("IsSubscribed")
            ->Name("Is Subscribed in List")
            ->MicroData("http://schema.org/Organization", "newsletter");

        //====================================================================//
        // Is Opt In
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("IsOptInPending")
            ->Name("Is Opt-In")
            ->MicroData("http://schema.org/Organization", "advertising")
            ->isReadOnly();

        //====================================================================//
        // Excluded from Campaigns
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("IsExcludedFromCampaigns")
            ->Name("Is Exluded from Campaigns")
            ->MicroData("http://schema.org/Organization", "excluded")
            ->isListed();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getCoreFields($key, $fieldName)
    {
        switch ($fieldName) {
            case 'Email':
            case 'Name':
                $this->getSimple($fieldName);

                break;
            case 'IsSubscribed':
                $this->out[$fieldName] = $this->isSubscribed() ;

                break;
            case 'IsExcludedFromCampaigns':
            case 'IsOptInPending':
                $this->getSimpleBool($fieldName);

                break;
            default:
                return;
        }
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $data      Field Data
     *
     * @return void
     */
    protected function setCoreFields($fieldName, $data)
    {
        switch ($fieldName) {
            case 'Email':
            case 'Name':
                $this->setSimple($fieldName, $data);

                break;
            case 'IsSubscribed':
                if ($this->isSubscribed() == $data) {
                    break;
                }
                $this->setIsSubscribed($data);

                break;
            case 'IsExcludedFromCampaigns':
                $this->setSimple($fieldName, $data);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Check if Member is Part of Current List
     *
     * @return boolean
     */
    private function isSubscribed()
    {
        $listId = API::getList();

        if (!isset($this->contactLists) || !is_array($this->contactLists)) {
            return false;
        }

        foreach ($this->contactLists as $list) {
            // @codingStandardsIgnoreStart
            if (!isset($list->IsUnsub) || $list->IsUnsub) {
                continue;
            }
            if (!isset($list->ListID) || ($list->ListID != $listId)) {
                continue;
            }
            // @codingStandardsIgnoreEnd

            return true;
        }

        return false;
    }

    /**
     * Update Member Status on Current List
     *
     * @param bool $data
     *
     * @return boolean
     */
    private function setIsSubscribed($data)
    {
        //====================================================================//
        // If Contact has no Id => Exit
        if (!isset($this->object->ID) || empty($this->object->ID)) {
            return false;
        }

        //====================================================================//
        // Re-Set As Subscribed
        if (!$this->isSubscribed() && $data) {
            $this->updateListStatus($this->object->ID, 'addforce');
        }
        //====================================================================//
        // UnSubscribe
        if ($this->isSubscribed() && !$data) {
            $this->updateListStatus($this->object->ID, 'unsub');
        }

        return true;
    }
}
