<?php

require_once "vendor/autoload.php";

$phpCount = new \Deimos\PHPCount(array(
    'default' => array(
        'user' => 'root',
        'password' => '',
        'driver' => 'pdo',
        'connection' => 'mysql:host=localhost;dbname=phpcount'
    )
));

var_dump($phpCount->getTotalAllHits());
var_dump($phpCount->getTotalTodayHits());

var_dump($phpCount->getIpAddressId());
var_dump($phpCount->getUserAgentId());

var_dump($phpCount->getPageId());
var_dump($phpCount->getHostnameId());

var_dump($phpCount->addHit());