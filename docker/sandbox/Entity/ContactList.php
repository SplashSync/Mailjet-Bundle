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
use App\Controller\ContactList\GetCollectionController;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Mailjet ContactList Entity - Stores contact list information.
 *
 * This entity represents a Mailjet contact list, which is used to organize
 * and segment contacts for targeted email campaigns. Contact lists allow
 * for better management of subscribers and more effective email marketing.
 *
 * Mailjet API Documentation:
 * https://dev.mailjet.com/email/guides/contact-management/#manage-contact-lists
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[API\ApiResource(
    uriTemplate: '/v3/REST/contactslist',
    operations: array(
        new API\GetCollection(
            controller: GetCollectionController::class,
            read: false,
        ),
        new API\Post(),
        new API\Get(uriTemplate: '/v3/REST/contactslist/{id}'),
        new API\Put(uriTemplate: '/v3/REST/contactslist/{id}', status: 204),
        new API\Delete(uriTemplate: '/v3/REST/contactslist/{id}', status: 204),
    )
)]
class ContactList
{
    use Traits\AuditTrait;

    /**
     * Unique identifier for the contact list.
     * This is the primary key in the database.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[SerializedName("ID")]
    public int $id;

    /**
     * Name of the contact list.
     * This is a human-readable identifier for the list.
     * Examples: "Newsletter", "Customers", "VIP Clients"
     */
    #[ORM\Column(length: 255)]
    #[SerializedName("Name")]
    public string $name;

    /**
     * Total number of subscribers in this list.
     * This field is automatically updated when contacts are added or removed.
     */
    #[ORM\Column(type: Types::INTEGER)]
    #[SerializedName("SubscriberCount")]
    public int $totalSubscribers = 0;

    /**
     * Constructor.
     * Initializes audit trail for the entity.
     */
    public function __construct()
    {
        $this->initAudit();
    }
}
