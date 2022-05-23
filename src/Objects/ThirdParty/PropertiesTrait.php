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
use stdClass;

/**
 * MailJet ThirdParty Custom Properties Fields
 */
trait PropertiesTrait
{
    /**
     * Collection of Known Attributes Names with Spacial Mapping
     *
     * This Collection is Public to Allow External Additions
     *
     * @var array<string, string[]>
     */
    public static array $knowAttributes = array(
        "firstname" => array("http://schema.org/Person", "familyName"),
        "lastname" => array("http://schema.org/Person", "givenName"),
    );

    /**
     * Storage for Members Properties
     *
     * @var array
     */
    protected array $contactData = array();

    /**
     * Base Attributes Metadata Item Name
     *
     * @var string
     */
    private static string $baseProp = "http://meta.schema.org/additionalType";

    /**
     * Attributes Type <> Splash Type Mapping
     *
     * @var array<string, string>
     */
    private static array $attrType = array(
        "str" => SPL_T_VARCHAR,
        "int" => SPL_T_INT,
        "float" => SPL_T_DOUBLE,
        "bool" => SPL_T_BOOL,
        "datetime" => SPL_T_DATETIME,
    );

    /**
     * @var null|array
     */
    private ?array $attrCache;

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildPropertiesFields(): void
    {
        //====================================================================//
        // Safety Check => Attributes Are Loaded
        $attributes = $this->getParameter("MembersAttributes");
        if (empty($attributes) || !is_iterable($attributes)) {
            return;
        }
        //====================================================================//
        // Create Attributes Fields
        $factory = $this->fieldsFactory();
        // @codingStandardsIgnoreStart
        foreach ($attributes as $attr) {
            //====================================================================//
            // Add Attribute to Fields
            $factory
                ->create(self::toSplashType($attr))
                ->identifier(strtolower($attr->Name))
                ->name($attr->Name)
                ->group("Attributes")
            ;
            //====================================================================//
            // Add Attribute MicroData
            $attrCode = strtolower($attr->Name);
            if (isset(self::$knowAttributes[$attrCode])) {
                $factory->microData(
                    self::$knowAttributes[$attrCode][0],
                    self::$knowAttributes[$attrCode][1]
                );

                continue;
            }
            $factory->microData(self::$baseProp, strtolower($attr->Name));
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Read requested Field
     *
     * @param string $key Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     *
     * @throws Exception
     */
    protected function getAttributesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Field is not an Attribute
        $attr = $this->isAttribute($fieldName);
        if (is_null($attr) || !isset($this->contactData)) {
            return;
        }
        //====================================================================//
        // Extract Attribute Value
        // @codingStandardsIgnoreStart
        $this->out[$fieldName] = $this->getAttributeValue($attr->Name, $attr->Datatype);
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param string|null $fieldData Field Data
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setAttributesFields(string $fieldName, ?string $fieldData): void
    {
        //====================================================================//
        // Field is not an Attribute
        $attr = $this->isAttribute($fieldName);
        if (is_null($attr) || !isset($this->contactData)) {
            return;
        }
        /**
         *====================================================================//
         * Extract Original Attribute Value
         *
         * @codingStandardsIgnoreStart
         */
        $origin = $this->getAttributeValue($attr->Name, $attr->Datatype);
        //====================================================================//
        // No Changes
        if ($origin == $fieldData) {
            unset($this->in[$fieldName]);

            return;
        }
        //====================================================================//
        // Update Attribute Value
        $this->setAttributeValue($attr->Name, $fieldData);
        // @codingStandardsIgnoreEnd
        unset($this->in[$fieldName]);
    }

    /**
     * Read requested Field Data
     *
     * @param string $name   Input List Key
     * @param string $format Field Identifier / Name
     *
     * @throws Exception
     *
     * @return null|bool|string
     */
    private function getAttributeValue(string $name, string $format): bool|string|null
    {
        //====================================================================//
        // Safety Check => Attributes Are Iterable
        if (!is_iterable($this->contactData)) {
            return null;
        }
        //====================================================================//
        // Walk on Member Attributes
        foreach ($this->contactData as $attrValue) {
            //====================================================================//
            // Search Requested Attribute
            // @codingStandardsIgnoreStart
            if ($attrValue->Name != $name) {
                continue;
            }
            //====================================================================//
            // Extract Attribute Value
            switch ($format) {
                case 'bool':
                    return ("true" == $attrValue->Value);
                case 'datetime':
                    if (empty($attrValue->Value)) {
                        return false;
                    }
                    $date = new DateTime($attrValue->Value);

                    return $date->format(SPL_T_DATETIMECAST);
                default:
                    return $attrValue->Value;
            }
            // @codingStandardsIgnoreEnd
        }

        return null;
    }

    /**
     * Write Requested Attribute Data
     *
     * @param string $name      Input List Key
     * @param null|string  $fieldData Field Data
     *
     * @return void
     */
    private function setAttributeValue(string $name, ?string $fieldData): void
    {
        //====================================================================//
        // Safety Check => Attributes Are Iterable
        if (!is_iterable($this->contactData)) {
            return;
        }
        //====================================================================//
        // Prepare New Attribute Value
        $newAttr = new stdClass();
        // @codingStandardsIgnoreStart
        $newAttr->Name = $name;
        $newAttr->Value = is_null($fieldData) ? "" : (string) $fieldData;
        //====================================================================//
        // Walk on Member Attributes
        foreach ($this->contactData as $index => $attrValue) {
            //====================================================================//
            // Search Requested Attribute
            if ($attrValue->Name != $name) {
                // @codingStandardsIgnoreEnd
                continue;
            }
            //====================================================================//
            // Update Attribute Value
            $this->contactData[$index] = $newAttr;
            $this->needUpdate("contactData");

            return;
        }

        //====================================================================//
        // Add Attribute Value
        $this->contactData[] = $newAttr;
        $this->needUpdate("contactData");
    }

    /**
     * Check if this Attribute Exists
     *
     * @param string $fieldName
     *
     * @return null|stdClass
     */
    private function isAttribute(string $fieldName) : ?stdClass
    {
        //====================================================================//
        // Safety Check => Attributes Are Loaded
        if (empty($this->attrCache)) {
            $attributes = $this->getParameter("MembersAttributes");
            if (empty($attributes) || !is_array($attributes)) {
                return null;
            }
            $this->attrCache = $attributes;
        }

        foreach ($this->attrCache as $attr) {
            // @codingStandardsIgnoreStart
            if ($fieldName == strtolower($attr->Name)) {
                return $attr;
            }
            // @codingStandardsIgnoreEnd
        }

        return null;
    }

    /**
     * Get Splash Attribute Type Name
     *
     * @param stdClass $attribute
     *
     * @return string
     */
    private static function toSplashType(stdClass $attribute): string
    {
        //====================================================================//
        // From mapping
        // @codingStandardsIgnoreStart
        if (isset(self::$attrType[$attribute->Datatype])) {
            return self::$attrType[$attribute->Datatype];
        }
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Default Type
        return SPL_T_VARCHAR;
    }
}
