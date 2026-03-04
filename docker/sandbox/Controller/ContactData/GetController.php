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
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Mailjet API Sandbox - Get contact data (custom properties).
 */
#[AsController]
class GetController extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, int $id): JsonResponse
    {
        //====================================================================//
        // Find Contact
        $contact = $em->getRepository(Contact::class)->find($id);
        if (!$contact) {
            return new JsonResponse(array('ErrorInfo' => 'Contact not found'), 404);
        }

        //====================================================================//
        // Return Contact Data in Mailjet Format
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
}
