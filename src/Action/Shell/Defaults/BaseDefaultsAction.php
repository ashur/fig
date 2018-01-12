<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action\Shell\ShellAction;
use Fig\Engine;
use Fig\Exception;
use Fig\Shell\Shell;

abstract class BaseDefaultsAction extends ShellAction
{
	/**
	 * @var	string
	 */
	protected $domain;

	/**
	 * @var	string
	 */
	protected $key;

	/**
	 * @var	string
	 */
	protected $methodName;

	/**
	 * @var	string
	 */
	protected $type = 'Defaults';

	/**
	 * @var	string
	 */
	protected $value;

	/**
	 * Returns domain
	 *
	 * @return	string
	 */
	public function getDomain() : string
	{
		return $this->replaceVariablesInString( $this->domain );
	}

	/**
	 * Returns defaults key
	 *
	 * @return	string
	 */
	public function getKey() : string
	{
		if( $this->key == null )
		{
			throw new \OutOfBoundsException( 'Key is undefined' );
		}

		return $this->replaceVariablesInString( $this->key );
	}

	/**
	 * Returns method string as action subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return $this->methodName;
	}

	/**
	 * Returns value
	 *
	 * @return	string
	 */
	public function getValue() : string
	{
		if( $this->value == null )
		{
			throw new \OutOfBoundsException( 'Value is undefined' );
		}

		return $this->replaceVariablesInString( $this->value );
	}

	/**
	 * Returns whether key is defined
	 *
	 * @return	bool
	 */
	public function hasKey() : bool
	{
		return $this->key != null;
	}

	/**
	 * Returns whether value is defined
	 *
	 * @return	bool
	 */
	public function hasValue() : bool
	{
		return $this->value != null;
	}

	/**
	 * Runs pre-deployment tasks
	 *
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @throws	Fig\Exception\RuntimeException	If 'defaults' command not found
	 *
	 * @return	void
	 */
	public function preDeploy( Shell $shell )
	{
		if( !$shell->commandExists( 'defaults' ) )
		{
			$exceptionMessage = sprintf( Engine::STRING_ERROR_COMMANDNOTFOUND, 'defaults' );
			throw new Exception\RuntimeException( $exceptionMessage, Exception\RuntimeException::COMMAND_NOT_FOUND );
		}
	}
}
