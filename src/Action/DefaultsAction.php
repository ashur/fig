<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use Fig\Exception;

class DefaultsAction extends BaseAction
{
	const READ   = 1;
	const WRITE  = 2;
	const DELETE = 4;

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
	protected $type = 'Defaults';

	/**
	 * @param	string	$name
	 *
	 * @param	int	$method
	 *
	 * @param	string	$domain
	 *
	 * @param	string	$key
	 *
	 * @param	string	$value
	 *
	 * @return	void
	 */
	public function __construct( string $name, int $method, string $domain, string $key=null, string $value=null )
	{
		$this->name = $name;

		$this->method = $method;
		$this->domain = $domain;
		$this->key = $key;
		$this->value = $value;
	}

	/**
	 * Attempts to execute defaults command
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine )
	{
		/* Make sure the command exists before trying to execute it */
		if( !$engine->commandExists( 'defaults' ) )
		{
			$exceptionMessage = sprintf( Engine::STRING_ERROR_COMMANDNOTFOUND, 'defaults' );
			throw new Exception\RuntimeException( $exceptionMessage, Exception\RuntimeException::COMMAND_NOT_FOUND );
		}

		if( $this->method == self::WRITE && !$this->hasValue() )
		{
			throw new InvalidActionArgumentsException( 'defaults.method=write requires defaults.value' );
		}

		/* Build command */
		$commandArguments[] = $this->getMethodString();
		$commandArguments[] = $this->getDomain();

		if( $this->hasKey() )
		{
			$commandArguments[] = $this->getKey();
		}
		if( $this->hasValue() )
		{
			$commandArguments[] = $this->getValue();
		}

		/* Execute command */
		$result = $engine->executeCommand( 'defaults', $commandArguments );

		/* Populate output, error */
		$this->didError = $result['exitCode'] !== 0;

		if( count( $result['output'] ) == 0 )
		{
			$this->outputString = self::STRING_STATUS_SUCCESS;
		}
		else
		{
			$this->outputString = implode( PHP_EOL, $result['output'] );
		}

		/* Output value when writing */
		if( $this->method == self::WRITE )
		{
			$this->outputString = $this->getValue();
		}
	}

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
	 * Returns key
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
	 * Returns string representation of defaults method
	 *
	 * @throws	DomainException	If unknown method value is set
	 *
	 * @return	string
	 */
	protected function getMethodString() : string
	{
		switch( $this->method )
		{
			case self::READ:
				$methodString = 'read';
				break;

			case self::WRITE:
				$methodString = 'write';
				break;

			case self::DELETE:
				$methodString = 'delete';
				break;

			default:
				throw new \DomainException( "Unknown method: '{$this->method}'" );
				break;
		}

		return $methodString;
	}

	/**
	 * Returns method string as action subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return $this->getMethodString();
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
}
