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
use Splash\Connectors\Mailjet\Models\Api\Contact as ContactModel;
use Splash\Core\Client\Splash;
use Splash\Core\Interfaces\Object\PrimaryKeysAwareInterface;
use Splash\OpenApi\Models\Objects\AbstractRestAndMetadataObject;

/**
 * Mailjet Implementation of ThirdParty
 */
class ThirdParty extends AbstractRestAndMetadataObject implements PrimaryKeysAwareInterface
{
    use ThirdParty\CRUDTrait;
    use ThirdParty\ContactListTrait;
    use ThirdParty\PropertiesTrait;

    /**
     * @var ContactModel
     */
    protected object $object;

    /**
     * @var MailjetConnector
     */
    protected MailjetConnector $connector;

    /**
     * @inheritDoc
     */
    protected static bool $allowPushDeleted = false;

    /**
     * @inheritDoc
     */
    protected static bool $enablePushDeleted = false;

    /**
     * Class Constructor
     */
    public function __construct(MailjetConnector $connector)
    {
        parent::__construct(
            $visitor = $connector->getVisitor(ContactModel::class),
            $visitor->getMetadataAdapter(),
            ContactModel::class
        );
        $this->connector = $connector;
        //====================================================================//
        //  Load Translation File
        Splash::translator()->load('local');
    }

    /**
     * Override Default Modes for Sandbox
     */
    public static function setSandboxMode(): void
    {
        static::$enablePushDeleted = true;
        static::$allowPushDeleted = true;
    }
}