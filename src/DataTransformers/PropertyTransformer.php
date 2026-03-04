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

namespace Splash\Connectors\Mailjet\DataTransformers;

use DateTime;
use Exception;
use Splash\Core\Helpers\DatesHelper;
use stdClass;

/**
 * Transform Mailjet Contact Property Values between Splash and Mailjet Formats
 */
class PropertyTransformer
{
    /**
     * Convert Mailjet Property Value to Splash Value
     *
     * @param null|float|int|string $rawValue Raw value from Mailjet API
     *
     * @throws Exception
     */
    public static function toSplash(stdClass $attribute, null|string|float|int $rawValue): null|bool|string|float|int
    {
        if (null === $rawValue) {
            return null;
        }

        return match ($attribute->Datatype) {
            "bool" => is_string($rawValue) && ("true" === $rawValue),
            "float" => (float) $rawValue,
            "int" => (int) $rawValue,
            "datetime" => self::toSplashDateTime($rawValue),
            default => (string) $rawValue,
        };
    }

    /**
     * Convert Splash Value to Mailjet Property Value
     *
     * @param null|bool|float|int|string $value Splash field value
     */
    public static function toMailjet(stdClass $attribute, null|bool|string|float|int $value): null|string
    {
        if (null === $value) {
            return null;
        }

        return match ($attribute->Datatype) {
            "bool" => $value ? "true" : "false",
            default => (string) $value,
        };
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Convert DateTime string to Splash DateTime format
     *
     * @throws Exception
     */
    private static function toSplashDateTime(null|string|float|int $rawValue): null|string
    {
        if (empty($rawValue) || !is_string($rawValue)) {
            return null;
        }

        return DatesHelper::toDateTimeStr(new DateTime($rawValue));
    }
}
