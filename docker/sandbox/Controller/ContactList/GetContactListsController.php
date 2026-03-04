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

namespace App\Controller\ContactList;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Mailjet API Sandbox - Get contact's subscribed lists.
 *
 * Simulates GET /contact/{id}/getcontactslists endpoint.
 * Returns list memberships for a given contact.
 */
#[AsController]
class GetContactListsController extends AbstractController
{
    /**
     * Handle contact lists request.
     *
     * @param EntityManagerInterface $em Doctrine entity manager
     * @param int                    $id Contact ID
     *
     * @return JsonResponse Response with contact list memberships
     */
    public function __invoke(EntityManagerInterface $em, int $id): JsonResponse
    {
        //====================================================================//
        // Find Contact
        $contact = $em->getRepository(Contact::class)->find($id);
        if (!$contact) {
            return new JsonResponse(array('ErrorInfo' => 'Contact not found'), 404);
        }

        //====================================================================//
        // Build List Memberships Data
        $data = array();
        foreach ($contact->contactListIds as $listId) {
            $data[] = array(
                'ListID' => $listId,
                'IsActive' => true,
                'IsUnsub' => false,
            );
        }

        //====================================================================//
        // Return in Mailjet Format
        return new JsonResponse(array(
            'Count' => count($data),
            'Data' => $data,
            'Total' => count($data),
        ));
    }
}
