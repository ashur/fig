<?php

/*
 * This file is part of Fig
 */
namespace Fig\Shell;

class Shell
{
	/**
	 * Returns whether a given command exists
	 *
	 * @param	string	$command
	 *
	 * @return	bool
	 */
	public function commandExists( string $command ) : bool
	{
		$result = $this->executeCommand( 'which', [$command] );
		return $result->getExitCode() === 0;
	}

	/**
	 * Executes a command and returns the result
	 *
	 * @param	string	$command	Name of command — ex., 'echo'
	 *
	 * @param	array	$arguments	Arguments to pass to command — ex., ['-n', 'hello']
	 *
	 * @return	Fig\Shell\Result
	 */
	public function executeCommand( string $command, array $arguments ) : Result
	{
		$commandPieces = $arguments;
		array_unshift( $commandPieces, $command );

		foreach( $commandPieces as &$commandPiece )
		{
			$commandPiece = escapeshellarg( $commandPiece );
		}

		$commandString  = implode( ' ', $commandPieces );
		$commandString .= ' 2>&1'; // redirect STDERR to STDOUT

		exec( $commandString, $output, $exitCode );

		$result = new Result( $output, $exitCode );
		return $result;
	}
}
