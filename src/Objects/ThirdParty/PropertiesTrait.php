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

namespace Splash\Connectors\Mailjet\Objects\ThirdParty;

use Exception;
use Splash\Connectors\Mailjet\DataTransformers\PropertyTransformer;
use Splash\Connectors\Mailjet\Models\Api\Contact;
use Splash\OpenApi\Dictionary\ExtendedActionsTypes;
use Splash\OpenApi\Models\Mutation;
use stdClass;

/**
 * MailJet ThirdParty Custom Properties Fields
 */
trait PropertiesTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildPropertiesFields(): void
    {
        $this->connector
            ->getLocator()
            ->getPropertiesManager()
            ->buildPropertiesFields($this->fieldsFactory())
        ;
    }

    /**
     * Read Requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @throws Exception
     *
     * @return void
     */
    protected function getPropertiesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Field is not a Property
        if (!$attr = $this->isProperty($fieldName)) {
            return;
        }
        //====================================================================//
        // Ensure Properties are Loaded
        $manager = $this->connector->getLocator()->getPropertiesManager();
        $manager->loadContactProperties($this->object);
        //====================================================================//
        // Read & Transform Property Value
        $rawValue = $this->object->properties[$fieldName] ?? null;
        $this->out[$fieldName] = PropertyTransformer::toSplash($attr, $rawValue);
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string                     $fieldName Field Identifier / Name
     * @param null|bool|float|int|string $fieldData Field Data
     *
     * @return void
     */
    protected function setPropertiesFields(string $fieldName, null|bool|string|float|int $fieldData): void
    {
        //====================================================================//
        // Field is not a Property
        if (!$attr = $this->isProperty($fieldName)) {
            return;
        }
        //====================================================================//
        // Ensure Properties are Loaded
        $manager = $this->connector->getLocator()->getPropertiesManager();
        $manager->loadContactProperties($this->object);
        //====================================================================//
        // Transform Splash Value to Mailjet Value
        $mailjetValue = PropertyTransformer::toMailjet($attr, $fieldData);
        //====================================================================//
        // Compare & Update Property Value
        $origin = $this->object->properties[$fieldName] ?? null;
        if ($origin != $mailjetValue) {
            $this->object->properties[$fieldName] = $mailjetValue;
            $this->visitor->getExtendedActionsBuffer()->add(
                ExtendedActionsTypes::POST_UPDATE,
                $this->buildPropertyMutation($fieldName, $mailjetValue)
            );
            $this->needUpdate();
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Check if this Field is a Property
     */
    private function isProperty(string $fieldName): ?stdClass
    {
        return $this->connector
            ->getLocator()
            ->getPropertiesManager()
            ->findByFieldName($fieldName)
        ;
    }

    /**
     * Build a Mutation for a Contact Property Update
     */
    private function buildPropertyMutation(string $fieldName, ?string $mailjetValue): Mutation
    {
        return Mutation::update(
            Contact::class,
            sprintf("/contactdata/%s", $this->object->getId()),
            (string) $this->object->getId(),
        )->withData(array("Data" => array(array(
            "Name" => $fieldName,
            "Value" => $mailjetValue,
        ))));
    }
}
