<?php

/*
 * Set data directory
 */
$fig = new Fig\Fig( $dataDirectory );
$app->registerCommandObject( 'fig', $fig );
