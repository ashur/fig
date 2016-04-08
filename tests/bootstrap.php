<?php

$dirBase   = dirname( __DIR__ );
$dirApp    = $dirBase  . '/lib/fig';
$dirVendor = $dirBase . '/vendor';

/*
 * Initialize autoloading
 */
include_once( $dirVendor . '/huxtable/cli/src/CLI/Autoloader.php' );
Huxtable\CLI\Autoloader::register();

include_once( $dirVendor . '/huxtable/core/src/Core/Autoloader.php' );
Huxtable\Core\Autoloader::register();

include_once( $dirApp . '/Autoloader.php' );
Fig\Autoloader::register();
