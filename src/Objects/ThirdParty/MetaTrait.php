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

use DateTime;
use Exception;

/**
 * MailJet ThirdParty Meta Fields
 */
trait MetaTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMetaFields()
    {
        //====================================================================//
        // TRACEABILITY INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->identifier("CreatedAt")
            ->name("Date Created")
            ->group("Meta")
            ->microData("http://schema.org/DataFeedItem", "dateCreated")
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Last Change Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->identifier("LastUpdateAt")
            ->name("Last modification")
            ->group("Meta")
            ->microData("http://schema.org/DataFeedItem", "dateModified")
            ->isListed()
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @throws Exception
     *
     * @return void
     */
    protected function getMetaFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Does the Field Exists?
        if (!in_array($fieldName, array('CreatedAt', 'LastUpdateAt'), true)) {
            return;
        }
        //====================================================================//
        // Insert in Response
        $date = new DateTime($this->object->{$fieldName});
        $this->out[$fieldName] = $date->format(SPL_T_DATETIMECAST);
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }
}
