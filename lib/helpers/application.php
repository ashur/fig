<?php

use Cranberry\Core\File;

/*
 * Set data directory
 */
$dataDirectory = new File\Directory( '~/.fig' );
$app->setDataDirectory( $dataDirectory );
