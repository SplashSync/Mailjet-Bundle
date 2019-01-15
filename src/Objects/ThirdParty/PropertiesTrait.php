<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
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
use stdClass;

/**
 * MailJet ThirdParty Custom Properties Fields
 */
trait PropertiesTrait
{
    /**
     * Collection of Known Attributes Names with Spacial Mapping
     *
     * This Collection is Public to Allow External Additons
     *
     * @var array
     */
    public static $knowAttributes  =   array(
        "name" => array("http://schema.org/Organization", "legalName"),
        "firstname" => array("http://schema.org/Person", "familyName"),
        "lastname" => array("http://schema.org/Person", "givenName"),
    );
    
    /**
     * Storage for Members Properties
     *
     * @var array
     */
    protected $contactData = array();
    
    /**
     * Base Attributes Metadata Item Name
     *
     * @var string
     */
    private static $baseProp = "http://meta.schema.org/additionalType";
    
    /**
     * Attributes Type <> Splash Type Mapping
     *
     * @var array
     */
    private static $attrType = array(
        "str" => SPL_T_VARCHAR,
        "int" => SPL_T_INT,
        "float" => SPL_T_DOUBLE,
        "bool" => SPL_T_BOOL,
        "datetime" => SPL_T_DATETIME,
    );

    private $attrCache;
    
    /**
     * Build Fields using FieldFactory
     */
    protected function buildPropertiesFields()
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
                ->Identifier(strtolower($attr->Name))
                ->Name($attr->Name)
                ->Group("Attributes");
            
            //====================================================================//
            // Add Attribute MicroData
            $attrCode = strtolower($attr->Name);
            if (isset(static::$knowAttributes[$attrCode])) {
                $factory->MicroData(
                    static::$knowAttributes[$attrCode][0],
                    static::$knowAttributes[$attrCode][1]
                );

                continue;
            }
            $factory->MicroData(static::$baseProp, strtolower($attr->Name));
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getAttributesFields($key, $fieldName)
    {
        //====================================================================//
        // Field is not an Attribute
        $attr   =   $this->isAttribute($fieldName);
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
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setAttributesFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Field is not an Attribute
        $attr   =   $this->isAttribute($fieldName);
        if (is_null($attr) || !isset($this->contactData)) {
            return;
        }
        //====================================================================//
        // Init Attributes Array if Needed
        if (!isset($this->contactData)) {
            $this->contactData = array();
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
     * @return null|bool|float|int|string
     */
    private function getAttributeValue($name, $format)
    {
        //====================================================================//
        // Safety Check => Attributes Are Itterable
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
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setAttributeValue($name, $fieldData)
    {
        //====================================================================//
        // Safety Check => Attributes Are Itterable
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
            $this->attrCache = $this->getParameter("MembersAttributes");
            if (empty($this->attrCache) || !is_iterable($this->attrCache)) {
                return null;
            }
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
    private static function toSplashType($attribute)
    {
        //====================================================================//
        // From mapping
        // @codingStandardsIgnoreStart
        if (isset(static::$attrType[$attribute->Datatype])) {
            return static::$attrType[$attribute->Datatype];
        }
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Default Type
        return SPL_T_VARCHAR;
    }
}
