<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Cranberry\Filesystem;
use Fig\Engine;
use Fig\Exception;

class ReplaceFileAction extends BaseFileAction
{
	/**
	 * @var	string
	 */
	protected $sourcePath;

	/**
	 * @var	string
	 */
	protected $subtitle = 'replace';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$sourcePathname	Relative pathname of source asset
	 *
	 * @param	string	$targetPathname	Full pathname of deployment target
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $sourcePath, string $targetPath )
	{
		$this->name = $name;
		$this->sourcePath = $sourcePath;
		$this->targetPath = $targetPath;
	}

	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @throws	LogicException	If parent profile name undefined
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine )
	{
		/* If the parent profile name hasn't been set, Fig is broken somewhere */
		if( ($profileName = $this->getProfileName()) == null )
		{
			$exceptionMessage = 'Improperly configured Action object: Profile name undefined.';
			throw new \LogicException( $exceptionMessage );
		}

		try
		{
			$assetNode = $engine->getProfileAssetNode( $profileName, $this->getSourcePath() );
		}
		/* If the source asset doesn't exist, Engine will throw. */
		catch( Exception\RuntimeException $e )
		{
			$this->didError = true;
			$this->outputString = sprintf( 'Invalid asset definition: %s', $e->getMessage() );

			return;
		}

		/* Get the target node */
		$targetNodeType = null;

		if( $assetNode instanceof Filesystem\Directory )
		{
			$targetNodeType = Filesystem\Node::DIRECTORY;
		}
		if( $assetNode instanceof Filesystem\File )
		{
			$targetNodeType = Filesystem\Node::FILE;
		}
		if( $assetNode instanceof Filesystem\Link )
		{
			$targetNodeType = Filesystem\Node::LINK;
		}

		$targetNode = $engine->getFilesystemNodeFromPath( $this->getTargetPath(), $targetNodeType );

		/* If the target node's parent isn't writable, we can't proceed. */
		$targetNodeParent = $targetNode->getParent();
		if( !$targetNodeParent->isWritable() )
		{
			$this->didError = true;
			$this->outputString = sprintf( self::ERROR_STRING_INVALIDTARGET, $targetNodeParent->getPathname(), self::ERROR_STRING_PERMISSION_DENIED );

			return;
		}

		try
		{
			$targetNode->delete();
		}
		/* The target node is not deletable... */
		catch( Filesystem\Exception $e )
		{
			/* ...due to permissions; we can't proceed. */
			if( $e->getCode() == Filesystem\Node::ERROR_CODE_PERMISSIONS )
			{
				$this->didError = true;
				$this->outputString = sprintf( self::ERROR_STRING_UNDELETABLE_NODE, $targetNode->getPathname(), self::ERROR_STRING_PERMISSION_DENIED );

				return;
			}

			/* ...because it doesn't exist; that's OK, let's keep going! */
		}

		/* Copy the source into place */
		$assetNode->copyTo( $targetNode );

		/* Holy cats, we made it. */
		$this->didError = false;
		$this->outputString = self::STRING_STATUS_SUCCESS;
	}

	/**
	 * Returns target file path
	 *
	 * @return	string
	 */
	public function getSourcePath() : string
	{
		return $this->replaceVariablesInString( $this->sourcePath );
	}
}
