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

namespace Splash\Connectors\Mailjet\Models\Api;

use Splash\Connectors\Mailjet\Dictionary\WebhookEventTypes;
use Splash\Connectors\Mailjet\Dictionary\WebhookStatus;
use Splash\Core\Dictionary\SplFields;
use Splash\Metadata\Attributes as SPL;
use Splash\OpenApi\Attributes\Rest\RestResource;
use Splash\OpenApi\Dictionary\SerializerGroups as SplGroups;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Json Metadata Model for Mailjet WebHooks (EventCallbackUrl).
 */
#[SPL\SplashObject(
    type: "Webhook",
    name: "WebHook",
    description: "Mailjet WebHook",
    ico: "fa fa-plug",
)]
#[RestResource(
    collectionUri: "/eventcallbackurl",
    itemUri: "/eventcallbackurl/{id}",
)]
class Webhook
{
    /**
     * WebHook ID on API
     */
    #[Serializer\SerializedName("ID")]
    #[Serializer\Groups(SplGroups::READ)]
    public int $ID;

    /**
     * WebHook ID for Splash
     */
    #[Serializer\Groups(array(SplGroups::LIST))]
    #[Serializer\SerializedName("id")]
    public int $id;

    /**
     * WebHook Endpoint URL
     */
    #[Assert\NotBlank]
    #[Assert\Url]
    #[SPL\Field(
        type: SplFields::URL,
        name: "Url",
        desc: "WebHook endpoint URL",
    )]
    #[SPL\Flags(required: true, listed: true)]
    #[Serializer\Groups(SplGroups::ALL)]
    #[Serializer\SerializedName("Url")]
    public string $Url = '';

    /**
     * WebHook Event Type
     */
    #[SPL\Field(
        type: SplFields::VARCHAR,
        name: "Event Type",
        desc: "WebHook triggered event type",
    )]
    #[SPL\Choices(WebhookEventTypes::ALL)]
    #[Serializer\Groups(SplGroups::ALL)]
    #[Serializer\SerializedName("EventType")]
    #[SPL\Flags(required:true, listed: true)]
    public ?string $EventType = null;

    /**
     * WebHook Is Backup
     */
    #[SPL\Field(
        type: SplFields::BOOL,
        name: "Is Backup",
        desc: "Is this a backup URL",
    )]
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("IsBackup")]
    public bool $IsBackup = false;

    /**
     * WebHook Status (raw value from API)
     */
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("Status")]
    public ?string $Status = null;

    /**
     * WebHook Is Alive
     */
    #[SPL\Field(
        type: SplFields::BOOL,
        name: "Is Alive",
        desc: "WebHook is alive",
    )]
    #[SPL\Flags(listed: true)]
    #[Serializer\Groups(array(SplGroups::READ, SplGroups::LIST))]
    public ?bool $isAlive = null;

    //====================================================================//
    // Getters & Setters
    //====================================================================//

    /**
     * Get WebHook ID
     */
    public function getId(): int
    {
        return $this->ID;
    }

    /**
     * Get WebHook Is Alive from Status
     */
    public function getIsAlive(): bool
    {
        return WebhookStatus::ALIVE === $this->Status;
    }

    /**
     * Set WebHook Status from Is Alive
     */
    public function setIsAlive(bool $isAlive): void
    {
        $this->Status = $isAlive ? WebhookStatus::ALIVE : WebhookStatus::DEAD;
    }
}