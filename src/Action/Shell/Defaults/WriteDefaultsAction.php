<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action;
use Fig\Exception;
use Fig\Engine;
use Fig\Shell;

class WriteDefaultsAction extends AbstractDefaultsAction
{
	/**
	 * @var	string
	 */
	protected $methodName='write';

	/**
	 * @var	string
	 */
	protected $value;

	/**
	 * @param	string	$name
	 *
	 * @param	string	$domain
	 *
	 * @param	string	$key
	 *
	 * @param	string	$value
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $domain, string $key, string $value )
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->key = $key;
		$this->value = $value;
	}

	/**
	 * Attempts to execute `defaults write <domain> <key> <value>`
	 *
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @return	Fig\Action\Result
	 */
	public function deploy( Shell\Shell $shell ) : Action\Result
	{
		/* Make sure the command exists before trying to execute it */
		if( !$shell->commandExists( 'defaults' ) )
		{
			$actionOutput = sprintf( Shell\Shell::STRING_ERROR_COMMANDNOTFOUND, 'defaults' );

			$result = new Action\Result( $actionOutput, true );
			return $result;
		}

		/* Build command */
		$commandArguments[] = $this->methodName;
		$commandArguments[] = $this->getDomain();
		$commandArguments[] = $this->getKey();

		/* Explode value into discrete elements to prevent escaping multipart
		   values like `-bool TRUE` into a single string `'bool -TRUE'`. */
		$value = explode( ' ', $this->getValue() );
		$commandArguments = array_merge( $commandArguments, $value );

		/* Execute command */
		$shellResult = $shell->executeCommand( 'defaults', $commandArguments );

		/* Populate output and error using shell results */
		$didError = $shellResult->getExitCode() !== 0;

		if( $didError )
		{
			$actionOutput = implode( PHP_EOL, $shellResult->getOutput() );
		}
		else
		{
			$actionOutput = $this->getValue();
		}

		$result = new Action\Result( $actionOutput, $didError );
		$result->ignoreErrors( $this->ignoreErrors );
		$result->ignoreOutput( $this->ignoreOutput );

		return $result;
	}

	/**
	 * Returns value
	 *
	 * @return	string
	 */
	public function getValue() : string
	{
		return Engine::renderTemplate( $this->value, $this->vars );
	}
}
