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
	 * @param	int	$type	Cranberry\Filesystem\Node::DIRECTORY, FILE, or LINK
	 *
	 * @throws	Fig\NonExistentFilesystemPathException	If $path doesn't exist and $type isn't specified
	 *
	 * @return	Cranberry\Filesystem\Node
	 */
	public function getFilesystemNodeFromPath( string $path, int $type=null ) : Filesystem\Node
	{
		/* Instantiate to get automatic `~/` substitution for free */
		$node = new Filesystem\File( $path );

		try
		{
			$node = Filesystem\Node::createNodeFromPathname( $node->getPathname() );

			/* Node exists, so let's return it */
		}
		catch( Filesystem\Exception $e )
		{
			/* Node does not exist, so let's create it manually */
			switch( $type )
			{
				case null:
					throw new NonExistentFilesystemPathException( "No such file or directory: {$path}" );
					break;

				case Filesystem\Node::DIRECTORY:
					$node = new Filesystem\Directory( $path );
					break;

				case Filesystem\Node::FILE:
					$node = new Filesystem\File( $path );
					break;

				case Filesystem\Node::LINK:
					$node = new Filesystem\Link( $path );
					break;
			}
		}

		return $node;
	}
}
