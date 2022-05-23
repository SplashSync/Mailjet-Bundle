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
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->identifier("Email")
            ->name("Email")
            ->microData("http://schema.org/ContactPoint", "email")
            ->isRequired()
            ->isListed()
        ;
        //====================================================================//
        // Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Name")
            ->name("Username")
            ->microData("http://schema.org/Organization", "legalName")
        ;
        //====================================================================//
        // Subscribed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("IsSubscribed")
            ->name("Is Subscribed in List")
            ->microData("http://schema.org/Organization", "newsletter")
        ;
        //====================================================================//
        // Is Opt In
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("IsOptInPending")
            ->name("Is Opt-In")
            ->microData("http://schema.org/Organization", "advertising")
            ->isReadOnly()
        ;
        //====================================================================//
        // Excluded from Campaigns
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("IsExcludedFromCampaigns")
            ->name("Is Excluded from Campaigns")
            ->microData("http://schema.org/Organization", "excluded")
            ->isListed()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getCoreFields(string $key, string $fieldName): void
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
     * @param bool|string|null $fieldData      Field Data
     *
     * @return void
     */
    protected function setCoreFields(string $fieldName, bool|string|null $fieldData): void
    {
        switch ($fieldName) {
            case 'Email':
            case 'Name':
            case 'IsExcludedFromCampaigns':
                $this->setSimple($fieldName, $fieldData);

                break;
            case 'IsSubscribed':
                if ($this->isSubscribed() == !empty($fieldData)) {
                    break;
                }
                $this->setIsSubscribed(!empty($fieldData));

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Check if Member is Part of Current List
     *
     * @return bool
     */
    private function isSubscribed(): bool
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
     * @return bool
     */
    private function setIsSubscribed($data): bool
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
