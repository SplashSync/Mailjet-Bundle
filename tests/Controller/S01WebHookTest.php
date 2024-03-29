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

namespace Splash\Connectors\Mailjet\Test\Controller;

use Exception;
use Splash\Connectors\Mailjet\Services\MailjetConnector;
use Splash\Tests\Tools\TestCase;

/**
 * Test of Mailjet Connector WebHook Controller
 */
class S01WebHookTest extends TestCase
{
    const PING_RESPONSE = '{"success":true}';
    const MEMBER = "ThirdParty";
    const FAKE_EMAIL = "fake@exemple.com";
    const METHOD = "JSON";

    /**
     * Test WebHook For Ping
     *
     * @throws Exception
     *
     * @return void
     */
    public function testWebhookPing()
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("mailjet");
        $this->assertInstanceOf(MailjetConnector::class, $connector);

        //====================================================================//
        // Ping Action -> GET -> OK
        $this->assertPublicActionWorks($connector);
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        //====================================================================//
        // Ping Action -> POST -> KO
        $this->assertPublicActionFail($connector, null, array(), "POST");
        $this->assertPublicActionFail($connector, null, array(), self::METHOD);
        //====================================================================//
        // Ping Action -> PUT -> KO
        $this->assertPublicActionFail($connector, null, array(), "PUT");
    }

    /**
     * Test WebHook with Errors
     *
     * @throws Exception
     *
     * @return void
     */
    public function testWebhookErrors()
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("mailjet");
        $this->assertInstanceOf(MailjetConnector::class, $connector);

        //====================================================================//
        // Empty Contents
        //====================================================================//

        $this->assertPublicActionFail($connector, null, array(), "POST");
        $this->assertPublicActionFail($connector, null, array(), self::METHOD);

        //====================================================================//
        // GOOD LIST ID BUT GET METHOD
        //====================================================================//

        $this->assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => $connector->getParameter("ApiList")),
            "GET"
        );
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        //====================================================================//
        // WRONG LIST ID
        //====================================================================//

        $this->assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => "ThisIsWrong"),
            "GET"
        );
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        //====================================================================//
        // GOOD LIST ID BUT NO EVENT TYPE
        //====================================================================//

        $this->assertPublicActionFail(
            $connector,
            null,
            array("mj_list_id" => $connector->getParameter("ApiList")),
            "POST"
        );

        $this->assertPublicActionFail(
            $connector,
            null,
            array("mj_list_id" => $connector->getParameter("ApiList")),
            self::METHOD
        );

        //====================================================================//
        // GOOD LIST ID, GOOD EVENT, BUT NO CONTACT ID
        //====================================================================//

        $this->assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => "ThisIsWrong"),
            "POST"
        );
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        $this->assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => "ThisIsWrong"),
            self::METHOD
        );
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());
    }

    /**
     * Test WebHook Member Updates
     *
     * @dataProvider webHooksInputsProvider
     *
     * @param array  $data
     * @param string $objectType
     * @param string $action
     * @param string $objectId
     *
     * @throws Exception
     *
     * @return void
     */
    public function testWebhookRequest(array $data, string $objectType, string $action, string $objectId)
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("mailjet");
        $this->assertInstanceOf(MailjetConnector::class, $connector);

        //====================================================================//
        // Prepare Request
        $post = array_replace_recursive(
            array("mj_list_id" => $connector->getParameter("ApiList")),
            $data
        );

        //====================================================================//
        // POST MODE
        $this->assertPublicActionWorks($connector, null, $post, "POST");
        $this->assertEquals(
            json_encode(array("success" => true)),
            $this->getResponseContents()
        );
        $this->assertIsLastCommitted($action, $objectType, $objectId);

        //====================================================================//
        // JSON MODE
        $this->assertPublicActionWorks($connector, null, $post, self::METHOD);
        $this->assertEquals(
            json_encode(array("success" => true)),
            $this->getResponseContents()
        );
        $this->assertIsLastCommitted($action, $objectType, $objectId);
    }

    /**
     * Generate Fake Inputs for WebHook Requets
     *
     * @return array
     */
    public function webHooksInputsProvider(): array
    {
        $hooks = array();

        //====================================================================//
        // Generate Subscribe Events
        for ($i = 0; $i < 10; $i++) {
            //====================================================================//
            // Generate Random Contact Email
            $randEmail = uniqid().self::FAKE_EMAIL;
            //====================================================================//
            // Add WebHook Test
            $hooks[] = array(
                array(
                    "event" => "unsub",
                    "mj_contact_id" => md5($randEmail),
                    "email" => $randEmail,
                ),
                self::MEMBER,
                SPL_A_UPDATE,
                md5($randEmail),
            );
        }

        return $hooks;
    }
}
