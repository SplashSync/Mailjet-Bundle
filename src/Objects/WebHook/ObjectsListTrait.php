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

/**
 * Mailjet WebHook Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function objectsList(?string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Get User Lists from Api
        $rawData = API::get('eventcallbackurl');
        //====================================================================//
        // Request Failed
        if (null == $rawData) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response = array(
            // @codingStandardsIgnoreStart
            'meta' => array('current' => $rawData->Count, 'total' => $rawData->Total),
        );
        //====================================================================//
        // Parse Data in response
        foreach ($rawData->Data as $webhook) {
            $response[] = array(
                'id' => $webhook->ID,
                'Url' => $webhook->Url,
                // @codingStandardsIgnoreEnd
            );
        }

        return $response;
    }
}
