<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Fig\Action\BaseAction;
use Fig\Engine;
use Fig\Exception;
use Fig\NonExistentFilesystemPathException;

class DeleteFileAction extends BaseFileAction
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
	 * @param	Fig\Engine	$engine
	 *
	 * @throws	Fig\Exception\RuntimeException	If target exists but is not deletable
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine )
	{
		/* When deleting a node, we don't care what type it is or even whether
		   it exists... */
		try
		{
			/* ...so we don't try to infer or pass the type. */
			$targetNode = $engine->getFilesystemNodeFromPath( $this->getTargetPath() );

			/* If the node does exist, attempt to delete it. */
			$targetNode->delete();
		}

		/* If the node doesn't exist, we don't really care. Silently ignore the
		   exception thrown by Fig::Engine and finish up deployment. */
		catch( NonExistentFilesystemPathException $e ) {}

		/* If the node does exist but is not deletable, it will throw
		   Cranberry\Filesystem\Exception. */
		catch( \Cranberry\Filesystem\Exception $e )
		{
            /* Re-throw it as a Fig\Exception\RuntimeException for proper
			   handling up the stack */
			throw new Exception\RuntimeException( $e->getMessage(), Exception\RuntimeException::FILESYSTEM_PERMISSION_DENIED, $e );
		}

		$this->didError = false;
		$this->outputString = BaseAction::STRING_STATUS_SUCCESS;
	}
}
