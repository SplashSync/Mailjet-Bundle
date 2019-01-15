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

/**
 * MailJet ThirdParty Core Fields (Required)
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
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
     * @param string $Key       Input List Key
     * @param string $FieldName Field Identifier / Name
     *
     * @return none
     */
    protected function getCoreFields($Key, $FieldName)
    {
        switch ($FieldName) {
            case 'Email':
                $this->getSimple($FieldName);

                break;
            case 'IsSubscribed':
                $this->out[$FieldName] = $this->getIsSubscribed() ;

                break;
            case 'IsExcludedFromCampaigns':
            case 'IsOptInPending':
                $this->getSimpleBool($FieldName);

                break;
            default:
                return;
        }
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$Key]);
    }
    
    /**
     * Write Given Fields
     *
     * @param string $FieldName Field Identifier / Name
     * @param mixed  $Data      Field Data
     *
     * @return none
     */
    protected function setCoreFields($FieldName, $Data)
    {
        switch ($FieldName) {
            case 'Email':
                $this->setSimple($FieldName, $Data);

                break;
            case 'IsSubscribed':
                if ($this->getIsSubscribed() == $Data) {
                    break;
                }
                $this->setIsSubscribed($Data);

                break;
            case 'IsExcludedFromCampaigns':
                $this->setSimple($FieldName, $Data);

                break;
            default:
                return;
        }
        unset($this->in[$FieldName]);
    }
    
    protected function getIsSubscribed()
    {
        $listId =   API::getList();
                
        if (!isset($this->contactLists) || !is_array($this->contactLists)) {
            return false;
        }
        
        foreach ($this->contactLists as $List) {
            if (!isset($List->IsUnsub) || $List->IsUnsub) {
                continue;
            }
            if ($List->ListID != $listId) {
                continue;
            }

            return true;
        }
        
        return false;
    }
    
    protected function setIsSubscribed($data)
    {
        //====================================================================//
        // If Contact has no Id => Exit
        if (!isset($this->object->ID) || empty($this->object->ID)) {
            return false;
        }
       
        //====================================================================//
        // Re-Set As Subscribed
        if (!$this->getIsSubscribed() && $data) {
            $this->updateListStatus($this->object->ID, 'addforce');
        }
        //====================================================================//
        // UnSubscribe
        if ($this->getIsSubscribed() && !$data) {
            $this->updateListStatus($this->object->ID, 'unsub');
        }
        
        return true;
    }
}
