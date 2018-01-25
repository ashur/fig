<?php

/*
 * This file is part of Fig
 */
namespace Fig;

trait VarsTrait
{
	/**
	 * @var	array
	 */
	protected $vars=[];

	/**
	 * Returns array of key/value pairs
	 *
	 * @return	array
	 */
	public function getVars() : array
	{
		return $this->vars;
	}

	/**
	 * Sets variables using keys with scalar values
	 *
	 * @param	array	$vars	An array of keys with scalar values
	 *
	 * @throws	InvalidArgumentException	If non-scalar value encountered
	 *
	 * @return	void
	 */
	public function setVars( array $vars )
	{
		foreach( $vars as $key => $value )
		{
			if( !is_scalar( $value ) )
			{
				throw new \InvalidArgumentException( "Non-scalar value defined for variable '{$key}'" );
			}

			$this->vars[$key] = $value;
		}
	}
}
