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
 * the library `gelfgl` (a library creating connection to the graylog system)
 * written by Nir Simionovich and its respective list of contributors.
 */

//Graylog2 server host address
define('GELF_SERVER' ,'SERVER');
//Graylog2 port number this is my default ,change it ,and in /etc/graylog2.conf
define('GELF_PORT'   ,12201);   // as default
//Graylog2 end point (if you get to your server with additional part in the URI) EXAMPLE http://example.org/additional_part/
define('GELF_ENDPOINT' , '');
//Graylog2 comes with possible additional basic authentication , this is the user name
define('GELF_USER' ,'user');
//Graylog2 password for basic authentication
define('GELF_PASS' ,'pass');

?>