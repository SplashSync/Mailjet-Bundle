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

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Controller\MyProfile\GetController;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mailjet MyProfile Entity - Stores profile information (singleton).
 *
 * GET: custom controller for /v3/REST/myprofile endpoint.
 */
#[ORM\Entity]
#[API\ApiResource(
    uriTemplate: '/v3/REST/myprofile',
    operations: array(
        new API\Get(
            controller: GetController::class,
            read: false,
        ),
    )
)]
class MyProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public int $id;

    #[ORM\Column(nullable: false)]
    public string $email = 'test@example.com';

    #[ORM\Column(nullable: false)]
    public string $companyName = 'Test Company';

    #[ORM\Column(nullable: true)]
    public ?string $addressStreet = '123 Test Street';

    #[ORM\Column(nullable: true)]
    public ?string $addressPostalCode = '12345';

    #[ORM\Column(nullable: true)]
    public ?string $addressCity = 'Test City';

    #[ORM\Column(nullable: true)]
    public ?string $addressCountry = 'Test Country';

    #[ORM\Column(nullable: true)]
    public ?string $website = 'https://test.example.com';

    #[ORM\Column(nullable: true)]
    public ?string $contactPhone = '+1234567890';

    /**
     * Convert entity to array for API response
     */
    public function toArray(): array
    {
        return array(
            'ID' => $this->id,
            'Email' => $this->email,
            'CompanyName' => $this->companyName,
            'AddressStreet' => $this->addressStreet,
            'AddressPostalCode' => $this->addressPostalCode,
            'AddressCity' => $this->addressCity,
            'AddressCountry' => $this->addressCountry,
            'Website' => $this->website,
            'ContactPhone' => $this->contactPhone,
        );
    }
}