<?php

/*
 * This file is part of Fig
 */

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
