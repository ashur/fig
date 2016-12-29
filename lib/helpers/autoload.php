<?php

$pathApplicationBase = dirname( dirname( __DIR__ ) );
$pathApplicationVendor = $pathApplicationBase . '/vendor';

/*
 * Initialize vendor autoloading
 */
include_once( $pathApplicationVendor . '/cranberry/cli/autoload.php' );
include_once( $pathApplicationVendor . '/spyc/Spyc.php' );

function registerNamespaceAutoloader( $namespace )
{
	GLOBAL $pathApplicationBase;
	$pathApplicationSrc = $pathApplicationBase . "/lib/src/{$namespace}";

	/*
	 * Initialize autoloading
	 */
	include_once( $pathApplicationSrc . '/Autoloader.php' );
	Autoloader::register();
}
