<?php

/*
 * This file is part of Fig
 */

$cleanupActions = array(
	/*
	 * 0.2.2:
	 *   - Removed config.php
	 *   - Removed Huxtable
	 */
	'0.2.2' => function( $applicationDirectory )
	{
		return array(
			$applicationDirectory->childDir( 'lib' )->child( 'config.php' ),
			$applicationDirectory->childDir( 'vendor' )->childDir( 'Huxtable' )
		);
	},
);
