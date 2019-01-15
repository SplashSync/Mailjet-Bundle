<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Mailjet\Models;

use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use Httpful\Response;
use Splash\Core\SplashCore as Splash;
use stdClass;

/**
 * Mailjet Specific Helper
 *
 * Support for Managing ApiKey, ApiRequests, Hashs, Etc...
 */
class MailjetHelper
{
    /**
     * @var string
     */
//    const ENDPOINT = "https://api.mailjet.com/v3.1/send";
    const ENDPOINT = "https://api.mailjet.com/v3/REST/";
    
    /**
     * @var string
     */
    private static $apiList;
    
    /**
     * Get Current Mailjet List
     *
     * @return string
     */
    public static function getList(): string
    {
        return self::$apiList;
    }
    
    /**
     * Congigure Mailjet REST API
     *
     * @param string $apiKey
     * @param string $secretKey
     * @param string $apiList
     *
     * @return bool
     */
    public static function configure(string $apiKey, string $secretKey, string $apiList = null): bool
    {
        //====================================================================//
        // Store Current List to Use
        self::$apiList = is_string($apiList) ? $apiList : "";
        //====================================================================//
        // Configure API Template Request
        $template = Request::init()
            ->authenticateWith($apiKey, $secretKey)
            ->sendsJson()
            ->expectsJson()
            ->timeout(3)
            ;
        // Set it as a template
        Request::ini($template);

        return true;
    }
    
    /**
     * Ping Mailjet API Url as Annonymous User
     *
     * @return bool
     */
    public static function ping(): bool
    {
        //====================================================================//
        // Perform Ping Test
        try {
            $response = Request::get(self::ENDPOINT."user")
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }
        
        if (($response->code >= 200) && ($response->code < 500)) {
            return true;
        }

        return false;
    }
    
    /**
     * Ping Mailjet API Url with API Key (Logged User)
     *
     * @return bool
     */
    public static function connect(): bool
    {
        //====================================================================//
        // Perform Connect Test
        try {
            $response = Request::get(self::ENDPOINT."user")
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }
        //====================================================================//
        // Catch Errors inResponse
        self::catchErrors($response);
        //====================================================================//
        // Return Connect Result
        return (200 == $response->code);
    }
    
    /**
     * Mailjet API GET Request
     *
     * @param string $path API REST Path
     * @param array  $body Request Data
     *
     * @return null|stdClass
     */
    public static function get(string $path, array $body = null): ?stdClass
    {
        //====================================================================//
        // Prepare Uri
        $uri = self::ENDPOINT.$path;
        if (!empty($body)) {
            $uri .= "?".http_build_query($body);
        }
        //====================================================================//
        // Perform Request
        try {
            $response = Request::get($uri)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        
        //====================================================================//
        // Catch Errors inResponse
        return self::catchErrors($response) ? $response->body : null;
    }
    
    /**
     * Mailjet API PUT Request
     *
     * @param string   $path API REST Path
     * @param stdClass $body Request Data
     *
     * @return null|stdClass
     */
    public static function put(string $path, stdClass $body): ?stdClass
    {
        //====================================================================//
        // Perform Request
        try {
            $response = Request::put(self::ENDPOINT.$path)
                ->body($body)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors inResponse
        return self::catchErrors($response) ? $response->body : null;
    }
    
    /**
     * Mailjet API POST Request
     *
     * @param string   $path API REST Path
     * @param stdClass $body Request Data
     *
     * @return null|stdClass
     */
    public static function post(string $path, stdClass $body): ?stdClass
    {
        //====================================================================//
        // Perform Request
        try {
            $response = Request::post(self::ENDPOINT.$path)
                ->body($body)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors inResponse
        return self::catchErrors($response) ? $response->body : null;
    }
    
    /**
     * Mailjet API DELETE Request
     *
     * @param string $path API REST Path
     *
     * @return null|bool
     */
    public static function delete(string $path): ?bool
    {
        //====================================================================//
        // Perform Request
        try {
            $response = Request::delete(self::ENDPOINT.$path)->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors in Response
        return self::catchErrors($response) ? true : false;
    }
    
    /**
     * Analyze Mailjet Api Response & Push Errors to Splash Log
     *
     * @param Response $response
     *
     * @return bool TRUE is no Error
     */
    private static function catchErrors(Response $response) : bool
    {
        //====================================================================//
        // Check if Mailjet Response has Errors
        if (!$response->hasErrors()) {
            return true;
        }
        //====================================================================//
        //  Debug Informations
        if (true == SPLASH_DEBUG) {
            Splash::log()->www("[Mailjet] Full Response", $response);
        }
        if (!$response->hasBody()) {
            return false;
        }
        $body = $response->body;
        
        //====================================================================//
        // Store Mailjet Errors if present
        if (isset($body->ErrorMessage)) {
            Splash::log()->err($body->ErrorMessage);
        }
        if (isset($body->ErrorInfo) && !empty($body->ErrorInfo)) {
            Splash::log()->err($response->body->ErrorInfo);
        }
        
        //====================================================================//
        // Detect Mailjet Errors Details
        if (isset($body->Errors) && is_array($body->Errors)) {
            foreach ($body->Errors->Errors as $mjError) {
                Splash::log()->err($mjError->ErrorMessage);
            }
        }

        return false;
    }
}
