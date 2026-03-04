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
use Splash\Bundle\Phpunit\Assertions\ConnectorValidator;
use Splash\Bundle\Phpunit\ConnectorTestCase;
use Splash\Connectors\Mailjet\Connectors\MailjetConnector;
use Splash\Core\Dictionary\SplOperations;
use Splash\Validator\Assertions\Objects\CommitValidator;

/**
 * Test of Mailjet Connector WebHook Controller
 */
class S01WebHookTest extends ConnectorTestCase
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
        $connector = $this->getConnector("sandbox");
        $this->assertInstanceOf(MailjetConnector::class, $connector);

        //====================================================================//
        // Ping Action -> GET -> OK
        ConnectorValidator::assertPublicActionWorks($connector);
        $this->assertEquals(self::PING_RESPONSE, ConnectorValidator::getResponseContents());

        //====================================================================//
        // Ping Action -> POST -> KO
        ConnectorValidator::assertPublicActionFail($connector, null, array(), "POST");
        ConnectorValidator::assertPublicActionFail($connector, null, array(), self::METHOD);
        //====================================================================//
        // Ping Action -> PUT -> KO
        ConnectorValidator::assertPublicActionFail($connector, null, array(), "PUT");
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
        $connector = $this->getConnector("sandbox");
        $this->assertInstanceOf(MailjetConnector::class, $connector);

        //====================================================================//
        // Empty Contents
        //====================================================================//

        ConnectorValidator::assertPublicActionFail($connector, null, array(), "POST");
        ConnectorValidator::assertPublicActionFail($connector, null, array(), self::METHOD);

        //====================================================================//
        // GOOD LIST ID BUT GET METHOD
        //====================================================================//

        ConnectorValidator::assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => $connector->getParameter("ApiList")),
            "GET"
        );
        $this->assertEquals(self::PING_RESPONSE, ConnectorValidator::getResponseContents());

        //====================================================================//
        // WRONG LIST ID
        //====================================================================//

        ConnectorValidator::assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => "ThisIsWrong"),
            "GET"
        );
        $this->assertEquals(self::PING_RESPONSE, ConnectorValidator::getResponseContents());

        //====================================================================//
        // GOOD LIST ID BUT NO EVENT TYPE
        //====================================================================//

        ConnectorValidator::assertPublicActionFail(
            $connector,
            null,
            array("mj_list_id" => $connector->getParameter("ApiList")),
            "POST"
        );

        ConnectorValidator::assertPublicActionFail(
            $connector,
            null,
            array("mj_list_id" => $connector->getParameter("ApiList")),
            self::METHOD
        );

        //====================================================================//
        // GOOD LIST ID, GOOD EVENT, BUT NO CONTACT ID
        //====================================================================//

        ConnectorValidator::assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => "ThisIsWrong"),
            "POST"
        );
        $this->assertEquals(self::PING_RESPONSE, ConnectorValidator::getResponseContents());

        ConnectorValidator::assertPublicActionWorks(
            $connector,
            null,
            array("event" => "unsub", "mj_list_id" => "ThisIsWrong"),
            self::METHOD
        );
        $this->assertEquals(self::PING_RESPONSE, ConnectorValidator::getResponseContents());
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
        $connector = $this->getConnector("sandbox");
        $this->assertInstanceOf(MailjetConnector::class, $connector);

        //====================================================================//
        // Prepare Request
        $post = array_replace_recursive(
            array("mj_list_id" => $connector->getParameter("ApiList")),
            $data
        );

        //====================================================================//
        // POST MODE
        ConnectorValidator::assertPublicActionWorks($connector, null, $post, "POST");
        $this->assertEquals(
            json_encode(array("success" => true)),
            ConnectorValidator::getResponseContents()
        );
        CommitValidator::assertIsLastCommitted($action, $objectType, $objectId);

        //====================================================================//
        // JSON MODE
        ConnectorValidator::assertPublicActionWorks($connector, null, $post, self::METHOD);
        $this->assertEquals(
            json_encode(array("success" => true)),
            ConnectorValidator::getResponseContents()
        );
        CommitValidator::assertIsLastCommitted($action, $objectType, $objectId);
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
                SplOperations::UPDATE,
                md5($randEmail),
            );
        }

        return $hooks;
    }
}
