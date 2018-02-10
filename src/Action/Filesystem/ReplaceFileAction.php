<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Cranberry\Filesystem as CranberryFilesystem;
use Fig\Action;
use Fig\Template;
use Fig\Exception;
use Fig\Filesystem;

class ReplaceFileAction extends AbstractFileAction
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
	 * @param	Fig\Filesystem\Filesystem	$filesystem
	 *
	 * @return	Fig\Action\Result
	 */
	public function deployWithFilesystem( Filesystem\Filesystem $filesystem ) : Action\Result
	{
		$didError = false;
		$actionOutput = Action\Result::STRING_STATUS_SUCCESS;

		/* If the parent profile name hasn't been set, Fig is broken somewhere */
		if( ($profileName = $this->getProfileName()) == null )
		{
			$exceptionMessage = 'Improperly configured Action object: Profile name undefined.';
			throw new \LogicException( $exceptionMessage );
		}

		try
		{
			$assetNode = $filesystem->getProfileAssetNode( $profileName, $this->getSourcePath() );
		}
		/* If the source asset doesn't exist, Filesystem will throw. */
		catch( Exception\RuntimeException $e )
		{
			$actionOutput = sprintf( 'Invalid asset definition: %s', $e->getMessage() );

			/* Attempting to deploy a missing asset is different from other
			   runtime issues and should not be ignorable. Instead of continuing
			   and setting `ignoreErrors` and `ignoreOutput`, stop here. */
			$result = new Action\Result( $actionOutput, true );
			return $result;
		}

		/* Get the target node */
		$targetNodeType = null;

		if( $assetNode instanceof CranberryFilesystem\Directory )
		{
			$targetNodeType = CranberryFilesystem\Node::DIRECTORY;
		}
		if( $assetNode instanceof CranberryFilesystem\File )
		{
			$targetNodeType = CranberryFilesystem\Node::FILE;
		}
		if( $assetNode instanceof CranberryFilesystem\Link )
		{
			$targetNodeType = CranberryFilesystem\Node::LINK;
		}

		$targetNode = $filesystem->getFilesystemNodeFromPath( $this->getTargetPath(), $targetNodeType );

		/* If the target node's parent isn't writable, we can't proceed. */
		$targetNodeParent = $targetNode->getParent();
		if( !$targetNodeParent->isWritable() )
		{
			$didError = true;
			$actionOutput = sprintf( self::ERROR_STRING_INVALIDTARGET, $targetNodeParent->getPathname(), self::ERROR_STRING_PERMISSION_DENIED );
		}
		else
		{
			try
			{
				$targetNode->delete();

				/* Copy the source into place */
				$assetNode->copyTo( $targetNode );
			}
			/* The target node is not deletable... */
			catch( CranberryFilesystem\Exception $e )
			{
				/* ...due to permissions; we can't proceed. */
				if( $e->getCode() == CranberryFilesystem\Node::ERROR_CODE_PERMISSIONS )
				{
					$didError = true;
					$actionOutput = sprintf( self::ERROR_STRING_UNDELETABLE_NODE, $targetNode->getPathname(), self::ERROR_STRING_PERMISSION_DENIED );
				}
			}
		}

		$result = new Action\Result( $actionOutput, $didError );
		$result->ignoreErrors( $this->ignoreErrors );
		$result->ignoreOutput( $this->ignoreOutput );

		return $result;
	}

	/**
	 * Returns target file path
	 *
	 * @return	string
	 */
	public function getSourcePath() : string
	{
		return Template::render( $this->sourcePath, $this->vars );
	}
}
