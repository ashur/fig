<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell;

use Fig\Action;
use Fig\Action\AbstractDeployableAction;
use Fig\Engine;
use Fig\Exception;
use Fig\Shell;

class CommandAction extends AbstractDeployableAction
{
	use \Fig\Action\Shell\AbstractDeployWithShellTrait;

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
	 * @return	Fig\Action\Result
	 */
	public function deployWithShell( Shell\Shell $shell ) : Action\Result
	{
		/* Make sure the command exists before trying to execute it */
		if( !$shell->commandExists( $this->command ) )
		{
			$actionOutput = sprintf( Shell\Shell::STRING_ERROR_COMMANDNOTFOUND, $this->command );

			$result = new Action\Result( $actionOutput, true );
			return $result;
		}

		/* Execute command */
		$shellResult = $shell->executeCommand( $this->getCommand(), $this->getCommandArguments() );
		$shellOutput = $shellResult->getOutput();

		/* Populate output and error using shell results */
		if( count( $shellOutput ) == 0 )
		{
			$actionOutput = Action\Result::STRING_STATUS_SUCCESS;
		}
		else
		{
			$actionOutput = implode( PHP_EOL, $shellOutput );
		}

		$didError = $shellResult->getExitCode() !== 0;

		$result = new Action\Result( $actionOutput, $didError );
		$result->ignoreErrors( $this->ignoreErrors );
		$result->ignoreOutput( $this->ignoreOutput );

		return $result;
	}

	/**
	 * Returns command name
	 *
	 * @return	string
	 */
	public function getCommand() : string
	{
		return Engine::renderTemplate( $this->command, $this->vars );
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
			$commandArguments[] = Engine::renderTemplate( $commandArgument, $this->vars );
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
