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

namespace App\Controller\ContactData;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Mailjet API Sandbox - Update contact data (custom properties).
 */
#[AsController]
class UpdateController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $em, int $id): JsonResponse
    {
        //====================================================================//
        // Parse Request Body
        $data = json_decode($request->getContent(), true) ?: array();

        //====================================================================//
        // Find Contact
        $contact = $em->getRepository(Contact::class)->find($id);
        if (!$contact) {
            return new JsonResponse(array('ErrorInfo' => 'Contact not found'), 404);
        }

        //====================================================================//
        // Update Properties (upsert: update existing or add new)
        $incoming = $data['Data'] ?? array();
        foreach ($incoming as $property) {
            if (!isset($property['Name'])) {
                continue;
            }
            $this->upsertProperty($contact, $property['Name'], $property['Value'] ?? null);
        }

        //====================================================================//
        // Save Changes
        $em->flush();

        //====================================================================//
        // Return Updated Contact Data
        return new JsonResponse(array(
            'Count' => 1,
            'Data' => array(array(
                'ID' => $contact->id,
                'ContactID' => $contact->id,
                'Data' => $contact->contactData,
            )),
            'Total' => 1,
        ));
    }

    /**
     * Upsert a property value on a Contact.
     */
    private function upsertProperty(Contact $contact, string $name, ?string $value): void
    {
        foreach ($contact->contactData as $index => $existing) {
            if (($existing['Name'] ?? null) === $name) {
                $contact->contactData[$index]['Value'] = $value;

                return;
            }
        }
        //====================================================================//
        // Property not found, add it
        $contact->contactData[] = array('Name' => $name, 'Value' => $value);
    }
}
