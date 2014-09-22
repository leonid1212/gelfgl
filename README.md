gelfgl
======

Simple GrayLog2 PHP client with or without basic authentication option build on PEST HTTP requests

Dependencies
------------
These are the minimum requirements to have phpari installed on your server:

** PHP >= 5.3.9

** Composer

** PHP OpenSSL Module to connect using SSL (wss:// uris)

Additional dependencies are installed via Composer, these include:

** Reactphp   (http://reactphp.org/)

** ZF2 Logger (http://framework.zend.com/manual/2.0/en/modules/zend.log.overview.html)

Installation
------------
The recommended method of installation is using Composer. Add the following to your composer.json file:

```
  "require": {
        "php": ">=5.3.9",
        "educoder/pest": "1.0.0",
        "devristo/phpws": "dev-master"
    }
```

We recommend using the "dev-master" version at this point in time, as we are still under heavy development and testing.

Verify functionality
--------------------
The simplest way to verify that GelfGL is installed correctly is by using it. Here is a minimal script to ensure you every installed correctly:

```php

require_once "../vendor/autoload.php";
require_once "../gelfConfig.php";


try{
    //add additional fields in that manner
    $additionalFields = array(
        'testField1'=>"test1",
        'testField2'=>"test2"
    );

    $gelfGL             = new gelfgl($gelfServer = GELF_SERVER , $gelfPort = GELF_PORT); //no user name and password
    $response           = $gelfGL->logInsert("1.1", 'localhost', 'shortMessage', 'longMessage', null, 'info', 'gelf_facility', __LINE__, __FILE__, $additionalFields);

    echo json_encode($response); //the response should return null
}
catch(Exception $e){
    header('Content-Type: application/json');
    echo json_encode(array('status' => $e->getCode(), 'message' => $e->getMessage()));
}

```
The output should resemble the following:
```
[root@gelfgl]# php simplelog.php
null
```

Reporting Issues
--------------------
Please report issues directly via the Github project page.
