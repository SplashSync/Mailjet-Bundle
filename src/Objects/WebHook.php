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

use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Mailjet\Services\MailjetConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use stdClass;

/**
 * Mailjet Implementation of WebHooks
 */
class WebHook extends AbstractStandaloneObject
{
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use WebHook\CRUDTrait;
    use WebHook\CoreTrait;
    use WebHook\ObjectsListTrait;

    /**
     * Object Disable Flag. Override this flag to disable Object.
     *
     * {@inheritdoc}
     */
    protected static bool $disabled = true;

    /**
     * {@inheritdoc}
     */
    protected static string $name = "WebHook";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Mailjet WebHook";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-cogs";

    /**
     * @phpstan-var stdClass
     */
    protected object $object;

    /**
     * @var MailjetConnector
     */
    protected MailjetConnector $connector;

    /**
     * Class Constructor
     *
     * @param MailjetConnector $parentConnector
     */
    public function __construct(MailjetConnector $parentConnector)
    {
        $this->connector = $parentConnector;
    }
}
