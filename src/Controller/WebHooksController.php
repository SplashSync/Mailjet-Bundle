<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Mailjet\Controller;

use Psr\Log\LoggerInterface;
use Splash\Bundle\Models\AbstractConnector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Splash Mailjet WebHooks Actions Controller
 */
class WebHooksController extends Controller
{
    //====================================================================//
    //  MAILJET WEBHOOKS MANAGEMENT
    //====================================================================//

    /**
     * Execute WebHook Actions for A Mailjet Connector
     *
     * @param LoggerInterface   $logger
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return JsonResponse
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function indexAction(LoggerInterface $logger, Request $request, AbstractConnector $connector)
    {
        //====================================================================//
        // For Mailjet ping GET
        if ($request->isMethod('GET')) {
            $logger->notice(__CLASS__.'::'.__FUNCTION__.' MailJet Ping.', $request->attributes->all());

            return $this->prepareResponse(200);
        }

        //====================================================================//
        // Read, Validate & Extract Request Parameters
        $eventData = $this->extractData($request);

        //====================================================================//
        // Log MailJet Request
        $logger->info(__CLASS__.'::'.__FUNCTION__.' MailJet WebHook Received ', $eventData);

        //==============================================================================
        // Commit Changes
        $this->executeCommits($connector, $eventData);

        return $this->prepareResponse(200);
    }

    /**
     * Execute Changes Commits
     *
     * @param AbstractConnector $connector
     * @param array             $eventData
     */
    private function executeCommits(AbstractConnector $connector, $eventData) : void
    {
        //==============================================================================
        // Filter on Unsub Events
        if (!isset($eventData['event']) || ("unsub" != $eventData['event'])) {
            return;
        }
        //==============================================================================
        // Check is in Selected List
        if (!isset($eventData['mj_list_id']) || ($eventData['mj_list_id'] != $connector->getParameter('ApiList'))) {
            return;
        }
        //==============================================================================
        // Check Contact ID provided & Valid
        if (!isset($eventData['mj_contact_id']) || empty($eventData['mj_contact_id']) || !is_scalar($eventData['mj_contact_id'])) {
            return;
        }
        //==============================================================================
        // Commit Changes to Splash
        $connector->commit('ThirdParty', (string) $eventData['mj_contact_id'], SPL_A_UPDATE, "Mailjet API", "MailJet Contact has Unsubscribed");
    }

    /**
     * Extract Data from Resquest
     *
     * @param Request $request
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    private function extractData(Request $request): array
    {
        //==============================================================================
        // Safety Check => Data are here
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('Malformatted or missing data');
        }
        //==============================================================================
        // Decode Received Data
        $requestData = $request->request->all();
        //==============================================================================
        // Safety Check => Data are here
        if (empty($requestData) || !isset($requestData['event'])) {
            throw new BadRequestHttpException('Malformatted or missing data');
        }
        //==============================================================================
        // Return Request Data
        return $requestData;
    }

    /**
     * Preapare REST Json Response
     *
     * @param int $status
     *
     * @return JsonResponse
     */
    private function prepareResponse(int $status) :JsonResponse
    {
        return new JsonResponse(array('success' => true), $status);
    }
}
