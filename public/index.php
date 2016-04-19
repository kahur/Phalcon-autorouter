<?php
$allowedIp = array(
    '212.64.22.129'
);
ini_set("display_errors",1);
    //
    error_reporting(E_ALL);
    $debug = new \Phalcon\Debug();
    $debug->listen();
$ip = $_SERVER['REMOTE_ADDR'];
if(!in_array($ip, $allowedIp) && strpos($_SERVER['HTTP_HOST'],'loc.') !== false || strpos($_SERVER['HTTP_HOST'],'dev.') !== false )
{ 
    ini_set("display_errors",1);
    //
    error_reporting(E_ALL);
    $debug = new \Phalcon\Debug();
    $debug->listen();
}
else {
    ini_set("display_errors",0);
}

require_once '../app/bootstrap.php';