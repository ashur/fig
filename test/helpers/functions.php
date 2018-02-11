<?php

/*
 * This file is part of Fig
 */

/**
 * Returns an object representing a temporary directory for use during test
 * execution.
 *
 * If the directory does not exist, it will be created automatically.
 *
 * @return	Cranberry\Filesystem\Directory
 */
function getTemporaryDirectory() : Cranberry\Filesystem\Directory
{
	$path = sprintf( '%s/tmp', dirname( __DIR__ ) );
	$directory = new Cranberry\Filesystem\Directory( $path );

	if( !$directory->exists() )
	{
		$directory->create();
	}

	return $directory;
}

/**
 * Returns a (mostly) unique string
 *
 * @param	string	$prefix
 *
 * @return	string
 */
function getUniqueString( string $prefix ) : string
{
	usleep( 100 );
	return sprintf( '%s%s', $prefix, microtime( true ) );
}
