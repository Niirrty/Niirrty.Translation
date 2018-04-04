<?php


$loader = include dirname( __DIR__ ) . '/vendor/autoload.php';

$loader->add( 'Niirrty\\Translation\\Tests', __DIR__ );
$loader->register();
