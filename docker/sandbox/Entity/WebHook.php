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

namespace App\Entity;

use ApiPlatform\Metadata as API;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Mailjet WebHook Entity - Stores webhook information.
 *
 * Webhooks in Mailjet allow you to receive real-time notifications about
 * events that occur in your Mailjet account, such as email sends, opens,
 * clicks, bounces, etc. This entity represents a webhook configuration
 * that specifies which events to monitor and where to send notifications.
 *
 * Mailjet API Documentation:
 * https://dev.mailjet.com/email/guides/webhooks/
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[API\ApiResource(
    uriTemplate: '/v3/REST/eventcallbackurl',
    operations: array(
        new API\GetCollection(),
        new API\Post(),
        new API\Get(uriTemplate: '/v3/REST/eventcallbackurl/{id}'),
        new API\Put(uriTemplate: '/v3/REST/eventcallbackurl/{id}', status: 204),
        new API\Delete(uriTemplate: '/v3/REST/eventcallbackurl/{id}', status: 204),
    )
)]
class WebHook
{
    /**
     * Unique identifier for the webhook.
     * This is the primary key in the database.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Serializer\SerializedName("ID")]
    public int $id;

    /**
     * WebHook Endpoint URL
     */
    #[ORM\Column(length: 255)]
    #[Serializer\SerializedName("Url")]
    public string $url;

    /**
     * WebHook Event Type
     */
    #[ORM\Column(length: 50, nullable: true)]
    #[Serializer\SerializedName("EventType")]
    public ?string $eventType = null;

    /**
     * WebHook Is Backup
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    #[Serializer\SerializedName("IsBackup")]
    public bool $isBackup = false;

    /**
     * WebHook Status (alive / dead)
     */
    #[ORM\Column(length: 20)]
    #[Serializer\SerializedName("Status")]
    public string $status = 'alive';

    /**
     * Constructor.
     * Initializes audit trail for the entity.
     */
    public function __construct()
    {
        $this->initAudit();
    }
}
