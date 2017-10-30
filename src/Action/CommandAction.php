<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;

class CommandAction extends BaseAction
{
	const STRING_ERROR_COMMANDNOTFOUND = 'Command not found: %s';

	/**
	 * @var	string
	 */
	protected $command;

	/**
	 * @var	array
	 */
	protected $commandArguments;

	/**
	 * @param	string	$name
	 *
	 * @param	string	$command
	 *
	 * @param	array	$commandArguments
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $command, array $commandArguments=[] )
	{
		$this->name = $name;

		$this->command = $command;
		$this->commandArguments = $commandArguments;
	}

	/**
	 * Attempts to execute command with arguments
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine )
	{
		/* Make sure the command exists before trying to execute it */
		if( !$engine->commandExists( $this->command ) )
		{
			$exceptionMessage = sprintf( self::STRING_ERROR_COMMANDNOTFOUND, $this->command );
			throw new CommandNotFoundException( $exceptionMessage );
		}

		$result = $engine->executeCommand( $this->getCommand(), $this->getCommandArguments() );

		$this->didError = $result['exitCode'] !== 0;

		if( count( $result['output'] ) == 0 )
		{
			$this->outputString = self::STRING_STATUS_SUCCESS;
		}
		else
		{
			$this->outputString = implode( PHP_EOL, $result['output'] );
		}
	}

	/**
	 * Returns command name
	 *
	 * @return	string
	 */
	public function getCommand() : string
	{
		return $this->replaceVariablesInString( $this->command );
	}

	/**
	 * Returns command arguments
	 *
	 * @return	array
	 */
	public function getCommandArguments() : array
	{
		$commandArguments = [];
		foreach( $this->commandArguments as $commandArgument )
		{
			$commandArguments[] = $this->replaceVariablesInString( $commandArgument );
		}

		return $commandArguments;
	}
}
