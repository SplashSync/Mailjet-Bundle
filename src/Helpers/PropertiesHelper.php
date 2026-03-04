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

namespace Splash\Connectors\Mailjet\Helpers;

use Splash\Core\Dictionary\SplFields;
use Splash\Templates\ThirdPartyFields;
use stdClass;

/**
 * Mailjet Contact Properties Helper
 */
class PropertiesHelper
{
    /**
     * Attributes Type <> Splash Type Mapping
     *
     * @var array<string, string>
     */
    private static array $attrType = array(
        "str" => SplFields::VARCHAR,
        "int" => SplFields::INT,
        "float" => SplFields::DOUBLE,
        "bool" => SplFields::BOOL,
        "datetime" => SplFields::DATETIME,
    );

    /**
     * Get Splash Attribute Type Name
     */
    public static function toSplashType(stdClass $attribute): string
    {
        //====================================================================//
        // From mapping
        if (isset(self::$attrType[$attribute->Datatype])) {
            return self::$attrType[$attribute->Datatype];
        }
        //====================================================================//
        // Default Type
        return SplFields::VARCHAR;
    }

    /**
     * Get Splash Field Template for Known Mailjet Properties
     *
     * @return null|class-string
     */
    public static function getTemplate(stdClass $attribute): ?string
    {
        return match (strtolower($attribute->Name)) {
            "firstname" => ThirdPartyFields::FIRSTNAME,
            "lastname" => ThirdPartyFields::LASTNAME,
            "name" => ThirdPartyFields::LASTNAME,
            default => null,
        };
    }
}
