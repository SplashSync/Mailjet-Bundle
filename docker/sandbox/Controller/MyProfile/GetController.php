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

namespace App\Controller\MyProfile;

use App\Entity\MyProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Mailjet API Sandbox - Get My Profile.
 *
 * Simple controller that returns dummy profile data for /myprofile endpoint.
 */
#[AsController]
class GetController
{
    public function __construct(private EntityManagerInterface $em) {}

    public function __invoke(): JsonResponse
    {
        // Get or create profile (singleton pattern)
        $profile = $this->em->getRepository(MyProfile::class)->findOneBy(array());
        
        if (!$profile) {
            $profile = new MyProfile();
            $this->em->persist($profile);
            $this->em->flush();
        }
        
        // Return response with data in Mailjet API format
        return new JsonResponse(array('Data' => array($profile->toArray())));
    }
}