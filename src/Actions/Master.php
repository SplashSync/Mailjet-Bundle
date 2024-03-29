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

use Psr\Log\LoggerInterface;
use Splash\Bundle\Models\AbstractConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Splash Mailjet WebHooks Actions Controller
 */
class Master extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Execute WebHook Actions for A Mailjet Connector
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return JsonResponse
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function __invoke(Request $request, AbstractConnector $connector): JsonResponse
    {
        //====================================================================//
        // For Mailjet ping GET
        if ($request->isMethod('GET')) {
            $this->logger->notice(__CLASS__.'::'.__FUNCTION__.' MailJet Ping.', $request->attributes->all());

            return $this->prepareResponse(200);
        }

        //====================================================================//
        // Read, Validate & Extract Request Parameters
        $eventData = $this->extractData($request);

        //====================================================================//
        // Log MailJet Request
        $this->logger->info(__CLASS__.'::'.__FUNCTION__.' MailJet WebHook Received ', $eventData);

        //==============================================================================
        // Commit Changes
        $this->executeCommits($connector, $eventData);

        return $this->prepareResponse(200);
    }

    /**
     * Execute Changes Commits
     *
     * @param AbstractConnector $connector
     * @param array             $data
     */
    private function executeCommits(AbstractConnector $connector, $data) : void
    {
        //==============================================================================
        // Filter on Unsub Events
        if (!isset($data['event']) || ("unsub" != $data['event'])) {
            return;
        }
        //==============================================================================
        // Check is in Selected List
        if (!isset($data['mj_list_id']) || ($data['mj_list_id'] != $connector->getParameter('ApiList'))) {
            return;
        }
        //==============================================================================
        // Check Contact ID provided & Valid
        if (!isset($data['mj_contact_id']) || empty($data['mj_contact_id']) || !is_scalar($data['mj_contact_id'])) {
            return;
        }
        //==============================================================================
        // Commit Changes to Splash
        $connector->commit(
            'ThirdParty',
            (string) $data['mj_contact_id'],
            SPL_A_UPDATE,
            "Mailjet API",
            "MailJet Contact has Unsubscribed"
        );
    }

    /**
     * Extract Data from Request
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
            throw new BadRequestHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Decode Received Data
        /** @var array $requestData */
        $requestData = empty($request->request->all())
            ? json_decode($request->getContent(), true, 512, \JSON_BIGINT_AS_STRING)
            : $request->request->all()
        ;
        //==============================================================================
        // Safety Check => Data are here
        if (empty($requestData) || !isset($requestData['event'])) {
            throw new BadRequestHttpException('Malformed or missing data');
        }

        //==============================================================================
        // Return Request Data
        return $requestData;
    }

    /**
     * Prepare REST Json Response
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
