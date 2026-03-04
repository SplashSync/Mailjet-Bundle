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

use App\Entity\ContactList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Mailjet API Sandbox - List contact lists.
 *
 * This controller handles the listing of contact lists in the Mailjet API format.
 * It returns contact list data in the structure expected by Mailjet API clients.
 *
 * Mailjet API Reference:
 * https://dev.mailjet.com/email/guides/contact-management/#list-contacts
 */
#[AsController]
class GetCollectionController extends AbstractController
{
    /**
     * Handle contact list listing request.
     *
     * @param Request $request The HTTP request with pagination parameters
     * @param EntityManagerInterface $em Doctrine entity manager
     *
     * @return JsonResponse Response with paginated contact list data
     */
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Get pagination parameters from request
        // Default values match Mailjet API defaults
        $limit = $request->query->getInt('Limit', 10);
        $offset = $request->query->getInt('Offset', 0);

        $repository = $em->getRepository(ContactList::class);

        // Retrieve contact lists from database with pagination
        $contactLists = $repository->findBy(array(), null, $limit, $offset);
        // Count total records in database (not just current page)
        $total = $repository->count(array());

        // Format response data in Mailjet API structure
        $data = array();
        foreach ($contactLists as $contactList) {
            $data[] = array(
                'ID' => $contactList->id,
                'Name' => $contactList->name,
                'SubscriberCount' => $contactList->totalSubscribers,
                'CreatedAt' => $contactList->createdAt?->format('Y-m-d\TH:i:sP'),
            );
        }

        // Return response in Mailjet format: Count = page items, Total = all items
        return new JsonResponse(array(
            'Count' => count($data),
            'Data' => $data,
            'Total' => $total,
        ));
    }
}