<?php
/**
 * GelfGL - A PHP Class Library for interfacing GrayLog servers
 * Copyright (C) 2014  Nir Simionovich
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 * Also add information on how to contact you by electronic and paper mail.
 *
 * Greenfield Technologies Ltd., hereby disclaims all copyright interest in
 * the library `gelfgl` (a library creating connection to the graylog system)
 * written by Nir Simionovich and its respective list of contributors.
 */
class gelfgl
{
    private $grLevel;
    private $gelfObj;
    private $gelfServer;
    private $gelfPort;
    private $gelfEndPoint;
    private $gelfUser;
    private $gelfPass;


    private static $GELF_UDP_BUFFER  = 1048576;
    private static $EMERGENCY =  0;
    private static $ALERT     =  1;
    private static $CRITICAL  =  2;
    private static $ERROR     =  3;
    private static $WARNING   =  4;
    private static $NOTICE    =  5;
    private static $INFO      =  6;
    private static $DEBUG     =  7;
    private $method;


    public function __construct($gelfServer = null, $gelfPort = '12201', $gelfEndPoint = '', $gelfUser = null, $gelfPass = null)
    {
        try {
            if (is_null($gelfServer))
                throw new Exception('Gelf server is missing  is empty or missing');
            if (is_null($gelfPort))
                throw new Exception('Gelf port is missing  is empty or missing');

            /**
             * Set properties
             */
            $this->gelfServer   = $gelfServer;
            $this->gelfPort     = $gelfPort;
            $this->gelfEndPoint = $gelfEndPoint;
            $this->gelfUser     = $gelfUser;
            $this->gelfPass     = $gelfPass;


        } catch (Exception $e) {
            return array('status' => 503, 'message' => $e->getMessage(), 'line' => $e->getLine());
        }
    }

    /**
     * This function will create a Pest Object for connecting the GrayLog information the URI  will be form the following format :
     * http://graylog2.example.org:[port]/gelf (POST)
     * For Graylog2 versions before v0.20.0 you first need to enable the GELF HTTP input in your graylog2.conf:
     *
     *  http_enabled        = true
     *  http_listen_address = 0.0.0.0
     *  http_listen_port    = 12201
     *
     * @param $gelfServer - server IP
     * @param $gelfPort - server Port
     * @param $gelfEndPoint - endpoint (for now is /gelf)
     * @param $gelfUser - if authentication will be required (TODO)
     * @param $gelfPass - if authentication will be required (TODO)
     * @return array
     */
    private function connect($gelfServer, $gelfPort, $gelfEndPoint, $gelfUser = null, $gelfPass = null)
    {
        try {

            if (is_null($gelfUser) && is_null($gelfPass)) {
                $this->gelfObj = new PestJSON("http://" . $gelfServer . ":" . $gelfPort . $gelfEndPoint); //create dynamic object
            } else {
                $this->gelfObj = new PestJSON("http://" . $gelfServer . ":" . $gelfPort . $gelfEndPoint);
                $this->gelfObj->setupAuth($gelfUser, $gelfPass, "basic");
            }

        } catch (Exception $e) {
            return array('status' => 503, 'message' => $e->getMessage(), 'line' => $e->getLine());
        }
    }

    /**
     * This function processing the log insert according to the GELF specifications a payload example will be constructed as a json
     * in the following payload format :
     *  {
     *    "version": "1.1",
     *    "host": "example.org",
     *    "short_message": "A short message that helps you identify what is going on",
     *    "full_message": "Backtrace here\n\nmore stuff",
     *    "timestamp": 1385053862.3072,
     *    "level": 1,
     *    "_user_id": 9001,
     *    "_some_info": "foo",
     *    "_some_env_var": "bar"
     *  }
     *
     * Libraries SHOULD not allow to send id as additional field (_id). Graylog2 server nodes omit this field automatically.
     *
     * @param string $version - GELF spec version – "1.1"; MUST be set by client library.
     * @param string $host - the name of the host, source or application that sent this message; MUST be set by client library.
     * @param string $short_message - a short descriptive message; MUST be set by client library.
     * @param string $full_message - a long message that can i.e. contain a backtrace; optional.
     * @param int $timestamp - Seconds since UNIX epoch with optional decimal places for milliseconds; SHOULD be set by client library. Will be set to NOW by server if absent.
     * @param string $level - the level equal to the standard sysLog levels; optional, default is 1 (ALERT).
     * @param string $facility - optional, deprecated. Send as additional field instead.
     * @param string $line - the line in a file that caused the error (decimal); optional, deprecated. Send as additional field instead.
     * @param string $file - the file (with path if you want) that caused the error (string); optional, deprecated. Send as additional field instead.
     * @param array $additionalFields - every field you send and prefix with a _ (underscore) will be treated as an additional field. Allowed characters in field names are any word character (letter, number, underscore), dashes and dots. The verifying regular expression is: ^[\w\.\-]*$
     * @return array
     */
    function  logInsert($version = "1.1",
                        $host = "127.0.0.1",
                        $short_message = null,
                        $full_message = null,
                        $timestamp = null,
                        $level = 'alert',
                        $facility = 'gelf_facility',
                        $line = null,
                        $file = null,
                        $additionalFields = array()
    )
    {
        try {

            if (is_null($this->gelfUser)) {
                if (is_null($this->gelfPass)) {
                    $this->connect($this->gelfServer, $this->gelfPort, $this->gelfEndPoint);
                } else {
                    throw new Exception("GELF Basic Authentication information not accepted", 503);
                }
            } else {

                $this->connect($this->gelfServer, $this->gelfPort, $this->gelfEndPoint,$this->gelfUser,$this->gelfPass);

            }

            switch (strtoupper($level)) {
                case "EMER":
                case "EMERGENCY":
                    $this->grLevel = $this::$EMERGENCY;
                    break;
                case "ALERT":
                    $this->grLevel = $this::$ALERT;
                    break;
                case "CRIT":
                case "CRITICAL":
                    $this->grLevel = $this::$CRITICAL;
                    break;
                case "ERROR":
                    $this->grLevel = $this::$ERROR;
                    break;
                case "WARN":
                case "WARNING":
                    $this->grLevel = $this::$WARNING;
                    break;
                case "NOTE":
                case "NOTICE":
                    $this->grLevel = $this::$NOTICE;
                    break;
                case "INFO":
                    $this->grLevel = $this::$INFO;
                    break;
                case "DEBUG":
                    $this->grLevel = $this::$DEBUG;
                    break;
                default:
                    $this->grLevel = $this::$INFO;
            }

            $additionalFieldsArray = array();

            if (!is_null($additionalFields) && is_array($additionalFields)) {
                foreach ($additionalFields as $key => $val) {
                    $index = '_' . $key;
                    $additionalFieldsArray[$index] = $val;
                }
            }
            if (is_null($full_message)) {
                $full_message = array(
                    'host' => $host,
                    'version' => $version,
                    'short_message' => $short_message,
                    'facility' => $facility,
                    'level' => $this->grLevel,
                    '_line' => $line,
                    '_file' => $file

                );

                $full_message = array_merge($full_message, $additionalFieldsArray);
            }

            $postOBJ = array(
                'host'          => $host,
                'version'       => $version,
                'short_message' => $short_message,
                'full_message'  => $full_message,
                'facility'      => $facility,
                'level'         => $this->grLevel,
                'timestamp'     => $timestamp,
                '_line'         => $line,
                '_file'         => $file,
            );

            $postOBJ = array_merge($postOBJ, $additionalFieldsArray);
            $uri = '/gelf';
            $result = $this->gelfObj->post($uri, $postOBJ);

            return $result;
        } catch (Exception $e) {
            return array('status' => 503, 'message' => $e->getMessage(), 'line' => $e->getLine());
        }
    }


    /**
     * This function processing the log insert according to the GELF specifications a payload example will be constructed as a json
     * in the following payload format :
     *  {
     *    "version": "1.1",
     *    "host": "example.org",
     *    "short_message": "A short message that helps you identify what is going on",
     *    "full_message": "Backtrace here\n\nmore stuff",
     *    "timestamp": 1385053862.3072,
     *    "level": 1,
     *    "_user_id": 9001,
     *    "_some_info": "foo",
     *    "_some_env_var": "bar"
     *  }
     *
     * Libraries SHOULD not allow to send id as additional field (_id). Graylog2 server nodes omit this field automatically.
     *
     * @param string $version - GELF spec version – "1.1"; MUST be set by client library.
     * @param string $host - the name of the host, source or application that sent this message; MUST be set by client library.
     * @param string $short_message - a short descriptive message; MUST be set by client library.
     * @param string $full_message - a long message that can i.e. contain a backtrace; optional.
     * @param int    $timestamp - Seconds since UNIX epoch with optional decimal places for milliseconds; SHOULD be set by client library. Will be set to NOW by server if absent.
     * @param string $level - the level equal to the standard sysLog levels; optional, default is 1 (ALERT).
     * @param string $facility - optional, deprecated. Send as additional field instead.
     * @param string $line - the line in a file that caused the error (decimal); optional, deprecated. Send as additional field instead.
     * @param string $file - the file (with path if you want) that caused the error (string); optional, deprecated. Send as additional field instead.
     * @param array  $additionalFields - every field you send and prefix with a _ (underscore) will be treated as an additional field. Allowed characters in field names are any word character (letter, number, underscore), dashes and dots. The verifying regular expression is: ^[\w\.\-]*$
     * @return array
     */
    function  logInsertUDP($version = "1.1",
                           $host = "127.0.0.1",
                           $short_message = null,
                           $full_message = null,
                           $timestamp = null,
                           $level = 'alert',
                           $facility = 'gelf_facility',
                           $line = null,
                           $file = null,
                           $additionalFields = array()
    ){


        try {


            /**
             * Strip level
             */
            switch (strtoupper($level)) {
                case "EMER":
                case "EMERGENCY":
                    $this->grLevel = $this::$EMERGENCY;
                    break;
                case "ALERT":
                    $this->grLevel = $this::$ALERT;
                    break;
                case "CRIT":
                case "CRITICAL":
                    $this->grLevel = $this::$CRITICAL;
                    break;
                case "ERROR":
                    $this->grLevel = $this::$ERROR;
                    break;
                case "WARN":
                case "WARNING":
                    $this->grLevel = $this::$WARNING;
                    break;
                case "NOTE":
                case "NOTICE":
                    $this->grLevel = $this::$NOTICE;
                    break;
                case "INFO":
                    $this->grLevel = $this::$INFO;
                    break;
                case "DEBUG":
                    $this->grLevel = $this::$DEBUG;
                    break;
                default:
                    $this->grLevel = $this::$INFO;
            }

            $additionalFieldsArray = array();

            if (!is_null($additionalFields) && is_array($additionalFields)) {
                foreach ($additionalFields as $key => $val) {
                    $index = '_' . $key;
                    $additionalFieldsArray[$index] = $val;
                }
            }
            if (is_null($full_message)) {
                $full_message = array(
                    'host'          => $host,
                    'version'       => $version,
                    'short_message' => $short_message,
                    'facility'      => $facility,
                    'level'         => $this->grLevel,
                    '_line'         => $line,
                    '_file'         => $file

                );

                $full_message = array_merge($full_message, $additionalFieldsArray);
            }

            $gelfObject = array(
                'host'          => $host,
                'version'       => $version,
                'short_message' => $short_message,
                'full_message'  => $full_message,
                'facility'      => $facility,
                'level'         => $this->grLevel,
                'timestamp'     => $timestamp,
                '_line'         => $line,
                '_file'         => $file,
            );

            /**
             * This is the final UDP object
             */
            $gelfObject = array_merge($gelfObject, $additionalFieldsArray);
            /**
             * The buffer size , you can manage the buffer size at the grayLog
             * side by default no longer then 1048576
             */
            $buffer = strlen(json_encode($gelfObject));


            if($buffer > $this::$GELF_UDP_BUFFER)
                die("Buffer size exceeded the standart size, gelf will not be sent"); //something went wrong so exit(1)
            /**
             * Create the socket object
             */
            if (!($sock    = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
                $errorcode = socket_last_error();
                $errormsg  = socket_strerror($errorcode);

                die("Couldn't create socket: " . $errormsg . "\n".$errorcode); //something went wrong so exit(1)
            }

            echo "Socket created \n";
            /**
             * Send the GELF object to the server using UDP
             */
            if (!socket_sendto($sock, json_encode($gelfObject), $buffer, 0, $this->gelfServer, $this->gelfPort)) {
                $errorcode = socket_last_error();
                $errormsg  = socket_strerror($errorcode);
                die("Couldn't send data : " . $errormsg . "\n" .$errorcode); //something went wrong so exit(1)

            }

            echo "Gelf object sent \n";

            return false;

        } catch (Exception $e) {
            die(json_encode(array('status' => 503, 'message' => $e->getMessage(), 'line' => $e->getLine())));

        }
    }



}






