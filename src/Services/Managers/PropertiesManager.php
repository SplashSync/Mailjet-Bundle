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

namespace Splash\Connectors\Mailjet\Services\Managers;

use Splash\Connectors\Mailjet\Helpers\PropertiesHelper;
use Splash\Connectors\Mailjet\Models\Api\Contact;
use Splash\Connectors\Mailjet\Models\MailjetConnectorAwareTrait;
use Splash\Core\Components\FieldsFactory;
use stdClass;

/**
 * Manage Mailjet Contact Metadata (Custom Properties)
 */
class PropertiesManager
{
    use MailjetConnectorAwareTrait;

    /**
     * Contact Properties Details Storage Key
     */
    const string PROPERTIES_DETAILS = "ApiContactPropertiesDetails";

    /**
     * Base Attributes Metadata Item Type
     */
    const string ITEM_TYPE = "http://meta.schema.org/additionalType";

    /**
     * Fetch Contact Metadata Properties from Mailjet API
     */
    public function fetchContactProperties(): bool
    {
        //====================================================================//
        // Get Contact Metadata from Api
        $response = $this->getConnexion()->get('/contactmetadata');
        if (is_null($response) || empty($response["Data"]) || !is_array($response["Data"])) {
            return false;
        }
        /** @var array[] $data */
        $data = $response["Data"];
        //====================================================================//
        // Filter to keep only static properties
        $propertiesDetails = array_values(array_filter(
            $data,
            static fn (array $property) => self::isStaticProperty($property)
        ));
        //====================================================================//
        // Store in Connector Settings
        $this->getConnector()->setParameter(
            self::PROPERTIES_DETAILS,
            json_decode((string) json_encode($propertiesDetails))
        );
        //====================================================================//
        // Update Connector Settings
        $this->getConnector()->updateConfiguration();

        return true;
    }

    /**
     * Load Contact Properties from API if not already loaded
     *
     * @return null|array Contact properties data, null on failure
     */
    public function loadContactProperties(Contact $contact): ?array
    {
        //====================================================================//
        // Already Loaded
        if (null !== $contact->properties) {
            return $contact->properties;
        }
        //====================================================================//
        // Load Contact Properties from API
        $response = $this->getConnexion()->get(
            sprintf('/contactdata/%d', $contact->ID)
        );
        if (is_null($response) || empty($response["Data"]) || !is_array($response["Data"])) {
            return null;
        }
        /** @var array[] $data */
        $data = $response["Data"];
        //====================================================================//
        // Parse Properties as Key => Value
        $properties = array();
        foreach ($data[0]["Data"] ?? array() as $property) {
            if (is_array($property) && is_scalar($property["Name"] ?? null)) {
                $properties[strtolower((string) $property["Name"])] = $property["Value"] ?? null;
            }
        }
        //====================================================================//
        // Store Properties on Contact
        $contact->properties = $properties;

        return $contact->properties;
    }

    /**
     * Find a Property by its Field Name
     */
    public function findByFieldName(string $fieldName): ?stdClass
    {
        //====================================================================//
        // Walk on Contact Properties
        foreach ($this->getPropertiesDetails() as $attr) {
            if (strtolower($attr->Name) == $fieldName) {
                return $attr;
            }
        }

        return null;
    }

    /**
     * Build Fields using FieldFactory
     */
    public function buildPropertiesFields(FieldsFactory $factory): void
    {
        //====================================================================//
        // Create Properties Fields
        foreach ($this->getPropertiesDetails() as $attr) {
            $this->buildPropertyField($factory, $attr);
        }
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Get Properties Details as stdClass array (safe after serialization)
     *
     * @return stdClass[]
     */
    private function getPropertiesDetails(): array
    {
        $raw = $this->getConnector()->getParameter(self::PROPERTIES_DETAILS);
        if (empty($raw) || !is_array($raw)) {
            return array();
        }

        //====================================================================//
        // Ensure stdClass format (serialization may convert to arrays)
        return array_filter(array_map(
            static fn ($item) => $item instanceof stdClass
                ? $item
                : (is_array($item) ? (object) $item : null),
            $raw
        ));
    }

    /**
     * Check if a property is static (user-defined) and should be managed.
     *
     * @param array<string, mixed>|stdClass $property
     */
    private static function isStaticProperty(array|stdClass $property): bool
    {
        $nameSpace = is_array($property)
            ? ($property["NameSpace"] ?? "")
            : ($property->NameSpace ?? "")
        ;

        return empty($nameSpace) || "static" === $nameSpace;
    }

    /**
     * Build Field using FieldFactory
     */
    private function buildPropertyField(FieldsFactory $factory, stdClass $attr): void
    {
        $attrCode = strtolower($attr->Name);
        //====================================================================//
        // Check for Known Field Template
        if ($template = PropertiesHelper::getTemplate($attr)) {
            $factory->createFromTemplate($attrCode, $template);
        } else {
            //====================================================================//
            // Add Attribute to Fields
            $factory
                ->create(PropertiesHelper::toSplashType($attr))
                ->identifier($attrCode)
                ->name($attr->Name)
                ->microData(self::ITEM_TYPE, $attrCode)
            ;
        }
        //====================================================================//
        // Configure Field
        $factory->group("Attributes");
    }
}
