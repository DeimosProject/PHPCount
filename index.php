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

var_dump($phpCount->getRealIpAddress());