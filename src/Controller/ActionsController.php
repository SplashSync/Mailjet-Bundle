<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Mailjet\Controller;

use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Splash\Connectors\Mailjet\Services\MailjetConnector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

/**
 * Splash Mailjet Connector Actions Controller
 */
class ActionsController extends Controller
{
    use ActionsTrait;

    /**
     * Update User Connector WebHooks List
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function webhooksAction(Request $request, AbstractConnector $connector)
    {
        $result = false;
        //====================================================================//
        // Connector SelfTest
        if (($connector instanceof MailjetConnector) && $connector->selfTest()) {
            /** @var RouterInterface $router */
            $router = $this->get('router');
            //====================================================================//
            // Update WebHooks Config
            $result = $connector->updateWebHooks($router);
        }
        //====================================================================//
        // Inform User
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $this->addFlash(
            $result ? "success" : "danger",
            $translator->trans(
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
