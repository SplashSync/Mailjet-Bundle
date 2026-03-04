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

namespace App\Serializer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Wraps API Platform responses in Mailjet API format.
 *
 * Mailjet API always returns: {"Count": N, "Data": [...], "Total": N}
 *
 * Handles both single items and paginated collections.
 */
final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @var DenormalizerInterface|NormalizerInterface
     */
    private DenormalizerInterface|NormalizerInterface $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(
                sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class)
            );
        }

        $this->decorated = $decorated;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = array()): bool
    {
        //====================================================================//
        // Always handle Paginator collections
        if ($data instanceof PaginatorInterface) {
            return true;
        }

        //====================================================================//
        // Delegate to decorated normalizer for single items
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = array()
    ): array|string|int|float|bool|\ArrayObject|null {
        //====================================================================//
        // Handle paginated collections
        if ($data instanceof PaginatorInterface) {
            $items = array();
            foreach ($data as $item) {
                $items[] = $this->decorated->normalize($item, $format, $context);
            }

            return array(
                'Count' => count($items),
                'Data' => $items,
                'Total' => (int) $data->getTotalItems(),
            );
        }

        //====================================================================//
        // Handle single items - normalize then wrap in Mailjet format
        $result = $this->decorated->normalize($data, $format, $context);

        return array(
            'Count' => 1,
            'Data' => array($result),
            'Total' => 1,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = array()
    ): bool {
        return $this->decorated->supportsDenormalization($data, $type, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize(
        mixed $data,
        string $class,
        ?string $format = null,
        array $context = array()
    ): mixed {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return array(
            PaginatorInterface::class => true,
            '*' => false,
        );
    }
}
