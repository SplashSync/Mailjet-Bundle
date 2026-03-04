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
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Mailjet ContactMetadata Entity - Stores contact metadata information.
 *
 * This entity represents custom contact properties (metadata) in Mailjet.
 * Contact metadata allows you to store additional information about contacts
 * beyond the standard fields, such as name, phone number, birth date, etc.
 *
 * Mailjet API Documentation:
 * https://dev.mailjet.com/email/guides/contact-management/#manage-contact-properties
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[API\ApiResource(
    uriTemplate: '/v3/REST/contactmetadata',
    operations: array(
        new API\GetCollection(),
        new API\Post(),
        new API\Get(uriTemplate: '/v3/REST/contactmetadata/{id}'),
        new API\Put(uriTemplate: '/v3/REST/contactmetadata/{id}', status: 204),
        new API\Delete(uriTemplate: '/v3/REST/contactmetadata/{id}', status: 204),
    )
)]
class ContactMetadata
{
    use Traits\AuditTrait;

    /**
     * Unique identifier for the contact metadata.
     * This is the primary key in the database.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[SerializedName("ID")]
    public int $id;

    /**
     * Name of the contact property.
     * This is the key used to reference the property in contact data.
     * Examples: "FIRSTNAME", "LASTNAME", "BIRTHDATE", "COMPANY"
     */
    #[ORM\Column(length: 255)]
    #[SerializedName("Name")]
    public string $name;

    /**
     * Data type of the contact property.
     * Determines how the property value should be interpreted.
     * Possible values: 'str' (string), 'int' (integer), 'float', 'bool', 'datetime'
     */
    #[ORM\Column(length: 255)]
    #[SerializedName("Datatype")]
    public string $dataType;

    /**
     * Property namespace.
     * "static" = user-defined properties, "historic" = system/legacy properties.
     * Only "static" properties should be used for sync.
     */
    #[ORM\Column(length: 50)]
    #[SerializedName("NameSpace")]
    public string $nameSpace = 'static';

    /**
     * Constructor.
     * Initializes audit trail for the entity.
     */
    public function __construct()
    {
        $this->initAudit();
    }
}
