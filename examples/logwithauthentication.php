<?php
/**
 * gelfgl - A PHP Class Library for interfacing GrayLog servers
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
 * the library `GelfGL` (a library creating connection to the graylog system)
 * written by Nir Simionovich and its respective list of contributors.
 */
require_once "../vendor/autoload.php";
require_once "../gelfConfig.php";

try{

    //Additional information
     $additionalFields = array(
        'testField1'=>"test1",
        'testField2'=>"test2"
    );

    $gelfGL             = new gelfgl($gelfServer = GELF_SERVER , $gelfPort = GELF_PORT , $gelfEndPoint = GELF_ENDPOINT , $gelfUser = GELF_USER , $gelfPass =GELF_PASS);
    $response           = $gelfGL->logInsert("1.1", 'localhost', 'shortMessage', 'longMessage', null, 'info', 'gelf_facility', __LINE__, __FILE__, $additionalFields);

    echo json_encode($response);
}
catch(Exception $e){
    header('Content-Type: application/json');
    echo json_encode(array('status' => $e->getCode(), 'message' => $e->getMessage()));
}
?>