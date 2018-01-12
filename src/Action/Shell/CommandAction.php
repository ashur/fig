<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell;

use Fig\Engine;
use Fig\Exception;
use Fig\Shell;

class CommandAction extends ShellAction
{
	/**
	 * @var	string
	 */
	protected $command;

	/**
	 * @var	array
	 */
	protected $commandArguments;

	/**
	 * @var	string
	 */
	protected $type = 'Command';

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
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @return	void
	 */
	public function deploy( Shell\Shell $shell )
	{
		/* Make sure the command exists before trying to execute it */
		if( !$shell->commandExists( $this->command ) )
		{
			$this->didError = true;
			$this->outputString = sprintf( Engine::STRING_ERROR_COMMANDNOTFOUND, $this->command );

			return;
		}

		/* Execute command */
		$result = $shell->executeCommand( $this->getCommand(), $this->getCommandArguments() );

		/* Populate output, error */
		$this->didError = $result->getExitCode() !== 0;

		$output = $result->getOutput();
		if( count( $output ) == 0 )
		{
			$this->outputString = self::STRING_STATUS_SUCCESS;
		}
		else
		{
			$this->outputString = implode( PHP_EOL, $output );
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

	/**
	 * Returns command name as action subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return $this->getCommand();
	}
}
