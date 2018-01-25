<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Filesystem;

class Engine
{
	/**
	 * description
	 *
	 * @param	string	$template	Template to be rendered — ex., 'hello, {{who}}'
	 *
	 * @param	array	$vars		Array of keys with scalar values — ex., ['who' => 'world']
	 *
	 * @return	string
	 */
	static public function renderTemplate( string $template, array $vars ) : string
	{
		$format = '/\{\{\s*%s\s*\}\}/';
		$string = $template;

		/* Replace all defined variables with their corresponding value */
		foreach( $vars as $key => $value )
		{
			$pattern = sprintf( $format, $key );
			$string = preg_replace( $pattern, $value, $string );
		}

		/* Replace any remaining undefined variables with '' */
		$pattern = sprintf( $format, '[^\s\}]+' );	// \{\{\s*[^\s\}]+\s*\}\}
		$string = preg_replace( $pattern, '', $string );

		return $string;
	}
}
