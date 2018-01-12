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
	 * @var	Cranberry\Filesystem\Directory
	 */
	protected $figDirectory;

	/**
	 * @param	Cranberry\Filesystem\Directory	$figDirectory
	 *
	 * @return	void
	 */
	public function __construct( Filesystem\Directory $figDirectory )
	{
		$this->figDirectory = $figDirectory;
	}

	/**
	 * Creates Cranberry\Filesystem\Node object using given path
	 *
	 * @param	string	$path
	 *
	 * @param	int	$type	Cranberry\Filesystem\Node::DIRECTORY, FILE, or LINK
	 *
	 * @throws	Fig\Exception\RuntimeException	If $path doesn't exist and $type isn't specified
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
					throw new Exception\RuntimeException
					(
						"No such file or directory: {$path}",
						Exception\RuntimeException::FILESYSTEM_NODE_NOT_FOUND,
						$e
					);
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

	/**
	 * Returns Cranberry\Filesystem\Node object representing a given profile asset
	 *
	 * @param	string	$profileName
	 *
	 * @param	string	$assetName
	 *
	 * @throws	Fig\Exception\RuntimeException	If asset does not exist
	 *
	 * @return	Cranberry\Filesystem\Node
	 */
	public function getProfileAssetNode( string $profileName, string $assetName ) : Filesystem\Node
	{
		try
		{
			$profileAssetNode = $this->figDirectory
				->getChild( $profileName )
				->getChild( $assetName );
		}
		/* If $filename does not exist and no node $type is specified,
		   Cranberry\Filesystem\Directory::getChild will throw an exception
		   which we'll catch here and re-throw as a Fig exception */
		catch( \Exception $e )
		{
			throw new Exception\RuntimeException
			(
				"Missing profile asset: {$profileName}/{$assetName}",
				Exception\RuntimeException::FILESYSTEM_NODE_NOT_FOUND,
				$e
			);
		}

		return $profileAssetNode;
	}
}
