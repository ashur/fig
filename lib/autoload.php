<?php

/*
 * This file is part of Fig
 */
namespace Fig;

$pathBaseFig = __DIR__;
$pathSrcBot = $pathBaseFig . '/fig';

/*
 * Initialize autoloading
 */
include_once( $pathSrcBot . '/Autoloader.php' );
Autoloader::register();
