<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

abstract class AbstractDeployableAction extends AbstractAction
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
	 * @var	string
	 */
	protected $type;

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
	 * Returns action subtitle
	 *
	 * @return	string
	 */
	abstract public function getSubtitle() : string;

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
	 * @param	mixed	$ignoreErrors
	 *
	 * @return	void
	 */
	public function ignoreErrors( $ignoreErrors )
	{
		try
		{
			$this->setBooleanishValue( $this->ignoreErrors, $ignoreErrors );
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
	 * @param	mixed	$ignoreOutput
	 *
	 * @return	void
	 */
	public function ignoreOutput( $ignoreOutput )
	{
		try
		{
			$this->setBooleanishValue( $this->ignoreOutput, $ignoreOutput );
		}
		catch( \DomainException $e )
		{
			$exceptionMessage = 'Invalid value for ignore_output: ' . $e->getMessage();
			throw new \DomainException( $exceptionMessage, $e->getCode(), $e );
		}
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
