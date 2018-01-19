<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action;
use Fig\Exception;
use Fig\Shell;

class DeleteDefaultsAction extends AbstractDefaultsAction
{
	/**
	 * @var	string
	 */
	protected $methodName='delete';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$domain
	 *
	 * @param	string	$key
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $domain, string $key=null )
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->key = $key;
	}

	/**
	 * Attempts to execute `defaults read <domain> [<key>]`
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

		if( $this->hasKey() )
		{
			$commandArguments[] = $this->getKey();
		}

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
			$actionOutput = Action\Result::STRING_STATUS_SUCCESS;
		}

		$result = new Action\Result( $actionOutput, $didError );
		$result->ignoreErrors( $this->ignoreErrors );
		$result->ignoreOutput( $this->ignoreOutput );

		return $result;
	}
}
