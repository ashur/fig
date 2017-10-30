<?php

/*
 * This file is part of Fig
 */
namespace Fig;

class Engine
{
	const STRING_ERROR_COMMANDNOTFOUND = 'Command not found: %s';

	/**
	 * Returns whether a command exists on the host system
	 *
	 * @param	string	$command
	 *
	 * @return	bool
	 */
	public function commandExists( string $command ) : bool
	{
		$result = $this->executeCommand( 'which', [$command] );
		return $result['exitCode'] == 0;
	}

	/**
	 * Executes a command
	 *
	 * Returns an array ['output' => <array>, 'exitCode' => <int>]
	 *
	 * @param	string	$command
	 *
	 * @param	array	$arguments
	 *
	 * @return	array
	 */
	public function executeCommand( string $command, array $arguments=[] ) : array
	{
		$commandPieces = $arguments;
		array_unshift( $commandPieces, $command );

		foreach( $commandPieces as &$commandPiece )
		{
			$commandPiece = escapeshellarg( $commandPiece );
		}

		$commandString = implode( ' ', $commandPieces );
		exec( $commandString, $output, $exitCode );

		return [
			'output' => $output,
			'exitCode' => $exitCode
		];
	}
}
