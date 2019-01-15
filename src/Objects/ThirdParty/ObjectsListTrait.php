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

namespace   Splash\Connectors\Mailjet\Objects\ThirdParty;

use Splash\Connectors\Mailjet\Models\MailjetHelper as API;
use stdClass;

/**
 * Mailjet Users Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Prepare Parameters
        $body     =    array('ContactsList' => API::getList());
        if (isset($params["max"], $params["offset"])) {
            $body['Limit']    =   $params["max"];
            $body['Offset']   =   $params["offset"];
        }
        //====================================================================//
        // Get User Lists from Api
        $rawData  =   API::get('contact', $body);
        //====================================================================//
        // Request Failed
        if (null == $rawData) {
            return array( 'meta'    => array('Count' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response   =   array(
            // @codingStandardsIgnoreStart
            'meta'  => array('current' => $rawData->Count, 'total' => $this->countContacts()),
            // @codingStandardsIgnoreEnd
        );
        //====================================================================//
        // Parse Data in response
        // @codingStandardsIgnoreStart
        foreach ($rawData->Data as $member) {
            $response[]   = array(
                'id'                        =>      $member->ID,
                'Email'                     =>      $member->Email,
                'IsExcludedFromCampaigns'   =>      $member->IsExcludedFromCampaigns,
                'CreatedAt'                 =>      $member->CreatedAt,
                'LastUpdateAt'              =>      $member->LastUpdateAt,
            );
        }
        // @codingStandardsIgnoreEnd

        return $response;
    }
    
    /**
     * Count Number of Contacts in Current List
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function countContacts() : int
    {
        //====================================================================//
        // Prepare Parameters
        $body     =    array(
            'ContactsList' => API::getList(),
            'countOnly' => true,
        );
        //====================================================================//
        // Get User Lists from Api
        $rawData  =   API::get('contact', $body);
        //====================================================================//
        // Request Failed
        // @codingStandardsIgnoreStart
        if ((null == $rawData) || (!is_numeric($rawData->Count))) {
            return 0;
        }

        return (int) $rawData->Count;
        // @codingStandardsIgnoreEnd
    }
}
