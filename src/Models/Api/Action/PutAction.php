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

use Splash\OpenApi\ApiResponse;
use Splash\OpenApi\Interfaces\Visitor\VisitorInterface;
use Splash\OpenApi\Models\Action\AbstractUpdateAction;
use Splash\OpenApi\Models\Visitor\AbstractRestVisitor;
use Webmozart\Assert\Assert;

/**
 * Update Object on Mailjet API using PUT method.
 *
 * Mailjet uses PUT (not PATCH) for updates.
 */
class PutAction extends AbstractUpdateAction
{
    /**
     * {@inheritDoc}
     */
    public function execute(VisitorInterface $visitor, string $objectId, object $object): ApiResponse
    {
        Assert::isInstanceOf($visitor, AbstractRestVisitor::class);
        //====================================================================//
        // Resolve Item Uri
        $itemUri = $visitor->getRestAdapter()
            ->getResourceNameResolver()
            ->resolveItemUri($visitor->getModel(), $objectId)
        ;
        if (!$itemUri) {
            return new ApiResponse($visitor);
        }
        //====================================================================//
        // Execute PUT Request
        $rawResponse = $visitor->getConnexion()->put(
            $itemUri,
            $this->extractData($visitor, $object)
        );
        if (null === $rawResponse) {
            return new ApiResponse($visitor);
        }

        return new ApiResponse($visitor, true, $rawResponse);
    }
}
