<?php

/*
 * This file is part of Fig
 */

$projectDir = dirname( __DIR__ );

/* Fig + Dependencies */
require_once( "{$projectDir}/vendor/autoload.php" );

/* FigTest */
require_once( __DIR__ . '/FigTest/Autoloader.php' );
FigTest\Autoloader::register();

/* Helpers */
require_once( __DIR__ . '/helpers/functions.php' );
