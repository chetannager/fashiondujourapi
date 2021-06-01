<?php
// Create and configure Slim app
require '../vendor/autoload.php';

$config = [
	'settings' => [
		'addContentLengthHeader' => true,
	]
];

$app = new \Slim\App($config);

// Require File
require 'fashiondujour.php';

// Run app
$app->run();



?>