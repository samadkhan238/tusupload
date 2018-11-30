<?php
error_reporting(1);
require 'vendor/autoload.php';
die("in ser");
$server   = new \TusPhp\Tus\Server('');
$server->setApiPath('/server/server.php'); // tus server endpoint.
//$server->setUploadDir('/tmp'); // uploads dir.
$response = $server->serve();
$response->send();
exit(0); // Exit from current PHP process.
