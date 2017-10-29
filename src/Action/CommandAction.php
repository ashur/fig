<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;

class CommandAction extends BaseAction
{
	const ERROR_STRING_COMMANDNOTFOUND = 'Command not found: %s';

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
		/* Make sure the command exists first */
		if( !$engine->commandExists( $this->command ) )
		{
			$exceptionMessage = sprintf( self::ERROR_STRING_COMMANDNOTFOUND, $this->command );
			throw new CommandNotFoundException( $exceptionMessage );
		}

		$engine->executeCommand( $this->command, $this->commandArguments );
	}
}
