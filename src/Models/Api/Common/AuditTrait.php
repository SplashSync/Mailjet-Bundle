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

namespace Splash\Connectors\Mailjet\Models\Api\Common;

use DateTime;
use Splash\Metadata\Attributes as SPL;
use Splash\OpenApi\Dictionary\SerializerGroups as SplGroups;
use Splash\Templates\Common\CommonFields;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Mailjet API Audit Dates (Created / Modified)
 */
trait AuditTrait
{
    /**
     * Creation Date
     */
    #[SPL\Template(CommonFields::DATE_CREATED)]
    #[Serializer\Groups(SplGroups::READ)]
    #[Serializer\SerializedName("CreatedAt")]
    public DateTime $CreatedAt;

    /**
     * Last Modification Date
     */
    #[SPL\Template(CommonFields::DATE_MODIFIED)]
    #[Serializer\Groups(SplGroups::READ)]
    #[Serializer\SerializedName("LastUpdateAt")]
    public ?DateTime $LastUpdateAt = null;

    /**
     * Set Last Updated Date (handles empty strings from API)
     */
    public function setLastUpdateAt(null|string|DateTime $lastUpdateAt): void
    {
        $this->LastUpdateAt = !empty($lastUpdateAt)
            ? ($lastUpdateAt instanceof DateTime ? $lastUpdateAt : new DateTime($lastUpdateAt))
            : null
        ;
    }
}
