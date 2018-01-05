<?php

/*
 * This file is part of FigTest
 */
namespace FigTest;

class Autoloader
{
	/**
	 * Registers `FigTest` autoloader
	 *
	 * @return	void
	 */
	static public function register()
	{
		spl_autoload_register( __CLASS__ . "::autoload" );
	}

	/**
	 * Attempts to autoload class
	 *
	 * @param	string	$class
	 *
	 * @return	void
	 */
	static public function autoload( string $class )
	{
		$namespace = basename( __DIR__ );

		if( substr( $class, 0, 1 ) == '\\' )
		{
			$class = substr( $class, 1 );
		}

		if( strpos( $class, $namespace ) == 0 )
		{
			// Convert class into filename
			$basename = str_replace( $namespace . '\\', '', $class );
			$basename = substr( $basename, 0, 1 ) == '\\' ? substr( $basename, 1 ) : $basename;
			$basename = str_replace( '\\', DIRECTORY_SEPARATOR, $basename ) . '.php';

			$filename = __DIR__ . DIRECTORY_SEPARATOR . $basename;

			if( file_exists( $filename ) )
			{
				require_once( $filename );
			}
		}
	}
}
