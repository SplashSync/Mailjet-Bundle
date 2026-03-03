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

namespace Splash\Connectors\Mailjet\Dictionary;

/**
 * Mailjet WebHook Event Types Definition
 */
class WebhookEventTypes
{
    const string OPEN = "open";
    const string CLICK = "click";
    const string BOUNCE = "bounce";
    const string SPAM = "spam";
    const string BLOCKED = "blocked";
    const string UNSUB = "unsub";
    const string SENT = "sent";

    /**
     * All available event types with labels
     *
     * @var array<string, string>
     */
    const array ALL = array(
        self::OPEN => "Open",
        self::CLICK => "Click",
        self::BOUNCE => "Bounce",
        self::SPAM => "Spam",
        self::BLOCKED => "Blocked",
        self::UNSUB => "Unsubscribe",
        self::SENT => "Sent",
    );
}
