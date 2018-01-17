<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Fig\Exception;
use Fig\Filesystem;

class DeleteFileAction extends AbstractFileAction
{
	/**
	 * @var	string
	 */
	protected $subtitle = 'delete';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$targetPath
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $targetPath )
	{
		$this->name = $name;
		$this->targetPath = $targetPath;
	}

	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Filesystem\Filesystem	$filesystem
	 *
	 * @return	void
	 */
	public function deploy( Filesystem\Filesystem $filesystem )
	{
		$this->didError = false;
		$this->outputString = self::STRING_STATUS_SUCCESS;

		/* When deleting a node, we don't care what type it is or even whether
		   it exists... */
		try
		{
			/* ...so we don't try to infer or pass the type. */
			$targetNode = $filesystem->getFilesystemNodeFromPath( $this->getTargetPath() );

			/* If the node does exist, attempt to delete it. */
			$targetNode->delete();
		}

		/* If the node doesn't exist, we don't really care; silently ignore the
		   exception thrown by Fig::Filesystem */
		catch( Exception\RuntimeException $e ) {}

		/* If the node does exist but is not deletable, it will throw
		   Cranberry\Filesystem\Exception. */
		catch( \Cranberry\Filesystem\Exception $e )
		{
            /* Action deployment has failed with an error */
			$this->didError = true;
			$this->outputString = $e->getMessage();
		}
	}
}
