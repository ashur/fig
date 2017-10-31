<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use Fig\Variablizable;

abstract class BaseAction extends Variablizable
{
	const STRING_STATUS_SUCCESS = 'OK';

	/**
	 * @var	bool
	 */
	protected $didError;

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
	 * @var	string
	 */
	protected $outputString;

	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	abstract public function deploy( Engine $engine );

	/**
	 * Returns whether deployment resulted in an error
	 *
	 * Will return NULL if deployment has not occurred. If deployment has occurred
	 * and errors are ignored, will always return true.
	 *
	 * @return	null|bool
	 */
	public function didError()
	{
		if( $this->ignoreErrors && $this->didError != null )
		{
			return false;
		}

		return $this->didError;
	}

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
	 * Returns output resulting from deployment
	 *
	 * Will return NULL if deployment has not occurred. If deployment has occurred
	 * and output is ignored, will always return 'OK'.
	 *
	 * @return	null|string
	 */
	public function getOutput()
	{
		if( $this->ignoreOutput && $this->outputString != null )
		{
			return self::STRING_STATUS_SUCCESS;
		}

		return $this->outputString;
	}

	/**
	 * Returns action type
	 *
	 * @return	string
	 */
	public function getType() : string
	{
		return $this->type;
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
