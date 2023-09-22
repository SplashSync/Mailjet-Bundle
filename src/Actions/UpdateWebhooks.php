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

namespace Splash\Connectors\Mailjet\Actions;

use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Splash\Connectors\Mailjet\Services\MailjetConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Splash Mailjet Connector Actions Controller
 */
class UpdateWebhooks extends AbstractController
{
    use ActionsTrait;

    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    /**
     * Update User Connector WebHooks List
     */
    public function __invoke(Request $request, AbstractConnector $connector): Response
    {
        $result = false;
        //====================================================================//
        // Connector SelfTest
        if (($connector instanceof MailjetConnector) && $connector->selfTest()) {
            //====================================================================//
            // Update WebHooks Config
            $result = $connector->updateWebHooks();
        }
        //====================================================================//
        // Inform User
        $this->addFlash(
            $result ? "success" : "danger",
            $this->translator->trans(
                $result ? "admin.webhooks.msg" : "admin.webhooks.err",
                array(),
                "MailjetBundle"
            )
        );
        //====================================================================//
        // Redirect Response
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return self::getDefaultResponse();
        }

        return new RedirectResponse($referer);
    }
}
