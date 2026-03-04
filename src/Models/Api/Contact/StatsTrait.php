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

namespace Splash\Connectors\Mailjet\Models\Api\Contact;

use DateTime;
use Splash\Core\Dictionary\SplFields;
use Splash\Metadata\Attributes as SPL;
use Splash\OpenApi\Dictionary\SerializerGroups as SplGroups;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Mailjet Contact Stats Fields (Delivered Count / Last Activity)
 */
trait StatsTrait
{
    /**
     * Delivered Count
     */
    #[SPL\Field(
        type: SplFields::INT,
        name: "Delivered Count",
        desc: "Number of delivered messages",
        group: "Meta",
    )]
    #[SPL\IsReadOnly]
    #[Serializer\Groups(array(SplGroups::READ))]
    #[Serializer\SerializedName("DeliveredCount")]
    public int $DeliveredCount = 0;

    /**
     * Last Activity Date
     */
    #[SPL\Field(
        type: SplFields::DATETIME,
        name: "Last Activity",
        desc: "Last activity date",
        group: "Meta",
    )]
    #[SPL\IsReadOnly]
    #[Serializer\Groups(array(SplGroups::READ))]
    #[Serializer\SerializedName("LastActivityAt")]
    public ?DateTime $LastActivityAt = null;

    /**
     * Set Last Activity Date (handles empty strings from API)
     */
    public function setLastActivityAt(null|string|DateTime $lastActivityAt): void
    {
        $this->LastActivityAt = !empty($lastActivityAt)
            ? ($lastActivityAt instanceof DateTime ? $lastActivityAt : new DateTime($lastActivityAt))
            : null
        ;
    }
}