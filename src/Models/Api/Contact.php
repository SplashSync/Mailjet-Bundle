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

use Splash\Connectors\Mailjet\Models\Api\Common\AuditTrait;
use Splash\Connectors\Mailjet\Models\Api\Contact\StatsTrait;
use Splash\Metadata\Attributes as SPL;
use Splash\OpenApi\Attributes\Rest\RestResource;
use Splash\OpenApi\Dictionary\SerializerGroups as SplGroups;
use Splash\Templates\ThirdPartyFields;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Json Metadata Model for Mailjet Contacts.
 *
 * @SuppressWarnings(CamelCasePropertyName)
 * @SuppressWarnings(ShortVariable)
 */
#[SPL\SplashObject(
    type: "ThirdParty",
    name: "Customer",
    description: "Mailjet Contact",
    ico: "fa fa-user",
    allowPushDeleted: null,
    enablePushDeleted: null
)]
#[RestResource(
    collectionUri: "/contact",
    itemUri: "/contact/{id}",
)]
class Contact
{
    use AuditTrait;
    use StatsTrait;

    /**
     * Contact ID on API
     */
    /** @codingStandardsIgnoreStart */
    #[Serializer\SerializedName("ID")]
    #[Serializer\Groups(SplGroups::READ)]
    public int $ID;
    /** @codingStandardsIgnoreEnd */

    /**
     * Contact ID for Splash
     */
    /** @codingStandardsIgnoreStart */
    #[Serializer\Groups(array(SplGroups::LIST))]
    #[Serializer\SerializedName("id")]
    public int $id;
    /** @codingStandardsIgnoreEnd */

    /**
     * Contact Email Address
     */
    /** @codingStandardsIgnoreStart */
    #[SPL\Template(ThirdPartyFields::EMAIL)]
    #[SPL\IsPrimary]
    #[SPL\IsRequired]
    #[Serializer\Groups(SplGroups::ALL)]
    #[Serializer\SerializedName("Email")]
    public string $Email;
    /** @codingStandardsIgnoreEnd */

    /**
     * Contact Name
     */
    /** @codingStandardsIgnoreStart */
    #[SPL\Field(
        type: "varchar",
        name: "Username",
        desc: "Contact Name",
    )]
    #[SPL\Microdata("http://schema.org/Organization", "legalName")]
    #[Serializer\Groups(SplGroups::DEFAULT)]
    #[Serializer\SerializedName("Name")]
    public ?string $Name = null;
    /** @codingStandardsIgnoreEnd */

    /**
     * Excluded from Campaigns Flag
     */
    /** @codingStandardsIgnoreStart */
    #[SPL\Template(ThirdPartyFields::NO_EMAIL)]
    #[Serializer\Groups(SplGroups::DEFAULT_LISTED)]
    #[Serializer\SerializedName("IsExcludedFromCampaigns")]
    public bool $IsExcludedFromCampaigns = false;
    /** @codingStandardsIgnoreEnd */

    /**
     * Opt-In Pending Flag
     */
    #[SPL\Field(
        type: "bool",
        name: "Is Opt-In",
        desc: "Contact Opt-In is pending",
    )]
    #[SPL\IsReadOnly]
    #[SPL\Microdata("http://schema.org/Organization", "advertising")]
    #[Serializer\Groups(array(SplGroups::READ))]
    #[Serializer\SerializedName("IsOptInPending")]
    public bool $isOptInPending = false;

    //====================================================================//
    // Additional Data
    //====================================================================//

    /**
     * Contact Custom Properties (Dynamic Fields)
     * Managed by PropertiesManager
     */
    public ?array $properties = null;

    /**
     * Contact Mailing Lists IDs (Current / To Add)
     * Managed by ListsManager
     */
    public array $listIds = array();

    //====================================================================//
    // Getters & Setters
    //====================================================================//

    /**
     * Get Contact ID
     */
    public function getId(): int
    {
        return $this->ID;
    }
}
