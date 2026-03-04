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

namespace App\Controller\Contact;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Mailjet API Sandbox - Get contact by ID or Email.
 *
 * Simulates Mailjet behavior: GET /contact/{id} accepts both numeric ID and email.
 */
#[AsController]
class GetController extends AbstractController
{
    /**
     * Handle contact GET request.
     *
     * @param EntityManagerInterface $em Doctrine entity manager
     * @param string                 $id Contact ID or email address
     *
     * @return JsonResponse Contact data in Mailjet format
     */
    public function __invoke(EntityManagerInterface $em, string $id): JsonResponse
    {
        //====================================================================//
        // Find Contact by ID or Email
        $contact = is_numeric($id)
            ? $em->getRepository(Contact::class)->find((int) $id)
            : $em->getRepository(Contact::class)->findOneBy(array('email' => urldecode($id)));

        if (!$contact) {
            return new JsonResponse(array('ErrorInfo' => 'Contact not found'), 404);
        }

        //====================================================================//
        // Return in Mailjet Format
        return new JsonResponse(array(
            'Count' => 1,
            'Data' => array(array(
                'ID' => $contact->id,
                'Email' => $contact->email,
                'Name' => $contact->name,
                'IsExcludedFromCampaigns' => $contact->isExcludedFromCampaigns,
                'CreatedAt' => $contact->createdAt->format('c'),
                'DeliveredCount' => $contact->getDeliveredCount(),
                'LastActivityAt' => $contact->getLastActivityAt()->format('c'),
            )),
            'Total' => 1,
        ));
    }
}
