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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Mailjet API Sandbox - Manage contact list memberships.
 *
 * Simulates POST /contact/{id}/managecontactslists endpoint.
 * Accepts ContactsLists array with ListID + Action (addnoforce, addforce, unsub, remove).
 */
#[AsController]
class UpdateContactListController extends AbstractController
{
    /**
     * Handle contact lists management request.
     *
     * @param Request                $request HTTP request with ContactsLists payload
     * @param EntityManagerInterface $em      Doctrine entity manager
     * @param int                    $id      Contact ID
     *
     * @return JsonResponse Response confirming the operation
     */
    public function __invoke(Request $request, EntityManagerInterface $em, int $id): JsonResponse
    {
        //====================================================================//
        // Find Contact
        $contact = $em->getRepository(Contact::class)->find($id);
        if (!$contact) {
            return new JsonResponse(array('ErrorInfo' => 'Contact not found'), 404);
        }

        //====================================================================//
        // Parse Request Body
        $body = json_decode((string) $request->getContent(), true);
        $contactsLists = $body['ContactsLists'] ?? array();

        //====================================================================//
        // Apply Actions
        $currentIds = $contact->contactListIds;
        foreach ($contactsLists as $entry) {
            $listId = (int) ($entry['ListID'] ?? 0);
            $action = $entry['Action'] ?? '';

            switch ($action) {
                case 'addforce':
                case 'addnoforce':
                    if (!in_array($listId, $currentIds, true)) {
                        $currentIds[] = $listId;
                    }

                    break;
                case 'unsub':
                case 'remove':
                    $currentIds = array_values(array_filter(
                        $currentIds,
                        fn (int $id) => $id !== $listId
                    ));

                    break;
            }
        }

        //====================================================================//
        // Save Changes
        $contact->contactListIds = $currentIds;
        $em->flush();

        return new JsonResponse(array(
            'Count' => 1,
            'Data' => array(array('ContactID' => $contact->id)),
            'Total' => 1,
        ));
    }
}
