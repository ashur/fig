<?php
/*
 * This file is part of Fig
 */

$projectDir = dirname( __DIR__ );

/* Fig autoloading */
require_once( "{$projectDir}/lib/src/Fig/Autoloader.php" );
Autoloader::register();

/* Vendor autoloading */
include_once( "{$projectDir}/vendor/cranberry/cli/autoload.php" );
