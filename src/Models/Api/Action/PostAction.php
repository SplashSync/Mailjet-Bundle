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

use Splash\OpenApi\Interfaces\Visitor\VisitorInterface;
use Splash\OpenApi\Models\Action\AbstractCreateAction;

/**
 * Create Object on Mailjet API.
 *
 * Unwraps Mailjet response format: {"Count": 1, "Data": [item], "Total": 1}
 */
class PostAction extends AbstractCreateAction
{
    /**
     * {@inheritDoc}
     */
    protected function hydrateData(VisitorInterface $visitor, array $rawResponse): object
    {
        //====================================================================//
        // Extract first item from Mailjet Data wrapper
        $itemData = (array) ($rawResponse["Data"][0] ?? array());

        return parent::hydrateData($visitor, $itemData);
    }
}
