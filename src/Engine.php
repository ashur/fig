<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Filesystem;

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

		$commandString  = implode( ' ', $commandPieces );
		$commandString .= ' 2>&1'; // redirect STDERR to STDOUT

		exec( $commandString, $output, $exitCode );

		return [
			'output' => $output,
			'exitCode' => $exitCode
		];
	}

	/**
	 * Creates Cranberry\Filesystem\Node object using given path
	 *
	 * @param	string	$path
	 *
	 * @throws	Fig\NonExistentFilesystemPathException	If path doesn't exist
	 *
	 * @return	Cranberry\Filesystem\Node
	 */
	public function getFilesystemNodeFromPath( string $path ) : Filesystem\Node
	{
		/* Instantiate to get automatic `~/` substitution for free */
		$node = new Filesystem\File( $path );

		if( $node->exists() )
		{
			if( is_dir( $node->getPathname() ) )
			{
				$node = new Filesystem\Directory( $path );
			}
		}
		else
		{
			throw new NonExistentFilesystemPathException( "No such file or directory: {$path}" );
		}

		return $node;
	}
}
