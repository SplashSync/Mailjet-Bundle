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

namespace Splash\Connectors\Mailjet\Services\Connexion;

use Httpful\Request;
use Httpful\Response;
use Splash\Core\Client\Splash;
use Splash\OpenApi\Interfaces\ErrorParserInterface;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\HttpFoundation\Response as SfResponse;

/**
 * Mailjet API Error Parser
 *
 * Detects HTTP errors and logs response body/headers to Splash Logger.
 */
#[When("never")]
class MailjetErrorParser implements ErrorParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function isErrored(Response $response): bool
    {
        //====================================================================//
        // Check if Response has Errors
        if (!$response->hasErrors()) {
            return false;
        }
        //====================================================================//
        // Extract Response Body
        $this->extractResponseBody($response);
        //====================================================================//
        // Detect Http Response Code
        Splash::log()->err((string) $response->code." => ".SfResponse::$statusTexts[(int) $response->code]);
        Splash::log()->err("Url => ".$response->meta_data['url']);
        //====================================================================//
        // Extract Request Body
        $this->extractRequestBody($response);

        return true;
    }

    /**
     * Extract Api Response Body & Push Errors to Splash Log
     */
    protected function extractResponseBody(Response $response): void
    {
        //====================================================================//
        // Try to decode response body as Json
        $decoded = json_decode($response->raw_body, true);
        //====================================================================//
        // Unable to decode => Store Raw Response
        if (!is_array($decoded)) {
            Splash::log()->err(html_entity_decode($response->raw_body));

            return;
        }
        //====================================================================//
        // Store Decoded Error Response
        // Mailjet specific error handling
        if (isset($decoded['ErrorMessage'])) {
            Splash::log()->err($decoded['ErrorMessage']);
        } elseif (isset($decoded['ErrorInfo'])) {
            Splash::log()->err($decoded['ErrorInfo']);
        } else {
            Splash::log()->err(print_r($decoded, true));
        }
    }

    /**
     * Extract Api Request Body & Push Warnings to Splash Log
     */
    protected function extractRequestBody(Response $response): void
    {
        //====================================================================//
        // Safety Check
        if (!$response->request instanceof Request) {
            return;
        }
        if (empty($response->request->payload)) {
            return;
        }
        //====================================================================//
        // Try to decode request body as Json
        $decoded = json_decode($response->request->payload, true);
        //====================================================================//
        // Payload was Decoded => Store Raw Request
        if (is_array($decoded) && !empty($decoded)) {
            Splash::log()->dump($decoded);
        }
    }
}
