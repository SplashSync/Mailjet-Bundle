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

namespace Splash\Connectors\Mailjet\Objects;

use Splash\Connectors\Mailjet\Connectors\MailjetConnector;
use Splash\Connectors\Mailjet\Dictionary\WebhookEventTypes;
use Splash\Connectors\Mailjet\Dictionary\WebhookStatus;
use Splash\Connectors\Mailjet\Models\Api\Webhook as WebhookModel;
use Splash\Core\Client\Splash;
use Splash\OpenApi\Models\Objects\AbstractRestAndMetadataObject;

/**
 * Mailjet Implementation of WebHooks
 */
class Webhook extends AbstractRestAndMetadataObject
{
    /**
     * @inheritDoc
     */
    protected static bool $disabled = true;

    /**
     * @var WebhookModel
     */
    protected object $object;

    /**
     * @var MailjetConnector
     */
    protected MailjetConnector $connector;

    /**
     * Class Constructor
     */
    public function __construct(MailjetConnector $connector)
    {
        parent::__construct(
            $visitor = $connector->getVisitor(WebhookModel::class),
            $visitor->getMetadataAdapter(),
            WebhookModel::class
        );
        $this->connector = $connector;
        //====================================================================//
        //  Load Translation File
        Splash::translator()->load('local');
    }

    /**
     * Override Default Mode
     */
    public static function setDisabled(bool $disabled = true): void
    {
        static::$disabled = $disabled;
    }

    /**
     * Create Splash WebHook from Url
     */
    public function createFromUrl(string $url): bool
    {
        return !empty($this->set(null, array(
            "Url" => $url,
            "EventType" => WebhookEventTypes::UNSUB,
            "IsBackup" => true,
            "Status" => WebhookStatus::ALIVE,
        )));
    }
}
