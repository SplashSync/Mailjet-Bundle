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
use App\Controller\ContactData\GetController as ContactDataGet;
use App\Controller\ContactData\UpdateController as ContactDataUpdate;
use App\Controller\ContactList\GetContactListsController;
use App\Controller\ContactList\UpdateContactListController;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Mailjet Contact Entity.
 *
 * Native API Platform CRUD on /v3/REST/contact.
 * ApiNormalizer wraps responses in Mailjet format (Count/Data/Total).
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[API\ApiResource(
    uriTemplate: '/v3/REST/contact',
    operations: array(
        new API\GetCollection(),
        new API\Post(),
    )
)]
#[API\ApiResource(
    uriTemplate: '/v3/REST/contact/{id}',
    operations: array(
        new API\Get(),
        new API\Put(extraProperties: array('standard_put' => false)),
        new API\Delete(status: 204, output: false),
    )
)]
#[API\ApiResource(
    uriTemplate: '/v3/REST/contactdata/{id}',
    operations: array(
        new API\Get(controller: ContactDataGet::class, read: false),
        new API\Put(controller: ContactDataUpdate::class, read: false),
    )
)]
#[API\ApiResource(
    uriTemplate: '/v3/REST/contact/{id}/getcontactslists',
    operations: array(
        new API\Get(controller: GetContactListsController::class, read: false),
    )
)]
#[API\ApiResource(
    uriTemplate: '/v3/REST/contact/{id}/managecontactslists',
    operations: array(
        new API\Post(controller: UpdateContactListController::class, read: false, write: false),
    )
)]
class Contact
{
    use Traits\AuditTrait;

    /**
     * Contact ID.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[SerializedName("ID")]
    public int $id;

    /**
     * Contact email address.
     */
    #[ORM\Column(unique: true, nullable: false)]
    #[SerializedName("Email")]
    public string $email;

    /**
     * Contact Name.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName("Name")]
    public ?string $name = null;

    /**
     * Excluded from email campaigns.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    #[SerializedName("IsExcludedFromCampaigns")]
    public bool $isExcludedFromCampaigns = false;

    /**
     * Contact custom properties (Name => Value pairs).
     *
     * @var array<int, array{Name: string, Value: ?string}>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Ignore]
    public array $contactData = array();

    /**
     * Contact list IDs (subscribed lists).
     * Managed via /contact/{id}/managecontactslists endpoint only.
     *
     * @var int[]
     */
    #[ORM\Column(type: Types::JSON)]
    #[Ignore]
    public array $contactListIds = array();

    public function __construct()
    {
        $this->initAudit();
    }

    /**
     * Get delivered count (random for sandbox).
     */
    #[SerializedName("DeliveredCount")]
    public function getDeliveredCount(): int
    {
        return rand(0, 1000);
    }

    /**
     * Get last activity date (returns updatedAt or createdAt).
     */
    #[SerializedName("LastActivityAt")]
    public function getLastActivityAt(): \DateTimeInterface
    {
        return $this->updatedAt ?? $this->createdAt;
    }
}
