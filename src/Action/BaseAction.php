<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Variablizable;

abstract class BaseAction extends Variablizable
{
	/**
	 * @var	bool
	 */
	protected $ignoreErrors=false;

	/**
	 * @var	bool
	 */
	protected $ignoreOutput=false;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * Returns action name string
	 *
	 * @return	string
	 */
	public function getName() : string
	{
		return $this->replaceVariablesInString( $this->name );
	}

	/**
	 * Specify whether errors should be ignored during deployment
	 *
	 * @param	bool	$shouldIgnoreErrors
	 *
	 * @return	void
	 */
	public function ignoreErrors( $shouldIgnoreErrors )
	{
		try
		{
			$this->setBooleanishValue( $this->ignoreErrors, $shouldIgnoreErrors );
		}
		catch( \DomainException $e )
		{
			$exceptionMessage = 'Invalid value for ignore_errors: ' . $e->getMessage();
			throw new \DomainException( $exceptionMessage, $e->getCode(), $e );
		}
	}

	/**
	 * Specify whether output should be ignored during deployment
	 *
	 * @param	bool	$shouldIgnoreOutput
	 *
	 * @return	void
	 */
	public function ignoreOutput( $shouldIgnoreOutput )
	{
		try
		{
			$this->setBooleanishValue( $this->ignoreOutput, $shouldIgnoreOutput );
		}
		catch( \DomainException $e )
		{
			$exceptionMessage = 'Invalid value for ignore_output: ' . $e->getMessage();
			throw new \DomainException( $exceptionMessage, $e->getCode(), $e );
		}
	}

	/**
	 * Attempts to set value of variable
	 *
	 * @param	mixed	$variable
	 *
	 * @param	mixed	$value
	 *
	 * @throws	DomainException	If $value is not "booleanish"
	 *
	 * @return	void
	 */
	protected function setBooleanishValue( &$variable, $value )
	{
		if( is_bool( $value ) )
		{
			$variable = $value;
			return;
		}

		if( is_string( $value ) )
		{
			$value = strtolower( $value );
			switch( $value )
			{
				case 'true':
				case 'yes':
					$variable = true;
					return;
					break;

				case 'false':
				case 'no':
					$variable = false;
					return;
					break;
			}
		}

		throw new \DomainException( "'{$value}' is not booleanish" );
	}

	/**
	 * Returns whether errors will ignored during deployment
	 *
	 * @return	bool
	 */
	public function willIgnoreErrors() : bool
	{
		return $this->ignoreErrors;
	}

	/**
	 * Returns whether output will ignored during deployment
	 *
	 * @return	bool
	 */
	public function willIgnoreOutput() : bool
	{
		return $this->ignoreOutput;
	}
}
