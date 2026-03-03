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

namespace Splash\Connectors\Mailjet\Models\Api\Action;

use Splash\OpenApi\Dictionary\ActionOptions;
use Splash\OpenApi\Interfaces\Visitor\VisitorInterface;
use Splash\OpenApi\Models\Action\AbstractListAction;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Read Objects List from Mailjet API.
 *
 * Unwraps Mailjet response format: {"Count": N, "Data": [...], "Total": N}
 */
class ListAction extends AbstractListAction
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        //====================================================================//
        // Mailjet uses Offset/Limit pagination (no page key)
        $resolver->setDefaults(array(
            ActionOptions::PAGE_KEY => null,
            ActionOptions::OFFSET_KEY => "Offset",
            ActionOptions::MAX_KEY => "Limit",
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function extractData(VisitorInterface $visitor, array $rawResponse): array
    {
        //====================================================================//
        // Extract items from Mailjet Data wrapper
        $items = $rawResponse["Data"] ?? array();
        if (!is_array($items)) {
            return array();
        }

        return parent::extractData($visitor, $items);
    }

    /**
     * {@inheritDoc}
     */
    protected function extractTotal(array $rawResponse, ?array $params = null): int
    {
        //====================================================================//
        // Extract total from Mailjet Total key
        if (isset($rawResponse["Total"])) {
            return (int) $rawResponse["Total"];
        }

        return parent::extractTotal($rawResponse, $params);
    }
}
