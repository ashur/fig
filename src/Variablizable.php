<?php

/*
 * This file is part of Fig
 */
namespace Fig;

abstract class Variablizable
{
	/**
	 * @var	array
	 */
	protected $variables=[];

	/**
	 * Returns a string with variables replaced by their corresponding values
	 *
	 * @param	string	$string
	 *
	 * @return	string
	 */
	public function replaceVariablesInString( string $string ) : string
	{
		$format = '/\{\{\s*%s\s*\}\}/';

		/* Replace all defined variables with their corresponding value */
		foreach( $this->variables as $key => $value )
		{
			$pattern = sprintf( $format, $key );
			$string = preg_replace( $pattern, $value, $string );
		}

		/* Replace any remaining undefined variables with '' */
		$pattern = sprintf( $format, '[^\s\}]+' );	// \{\{\s*[^\s\}]+\s*\}\}
		$string = preg_replace( $pattern, '', $string );

		return $string;
	}

	/**
	 * Sets variable key/value pairs
	 *
	 * @param	array	$variables
	 *
	 * @throws	InvalidArgumentException	If non-scalar value defined
	 *
	 * @return	void
	 */
	public function setVariables( array $variables )
	{
		foreach( $variables as $key => $value )
		{
			if( !is_scalar( $value ) )
			{
				throw new \InvalidArgumentException( "Non-scalar value defined for variable '{$key}'" );
			}

			$this->variables[$key] = $value;
		}
	}
}
