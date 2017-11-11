<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Fig\Action\BaseAction;
use Fig\Engine;
use Fig\Exception;

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
	 * @return	void
	 */
	public function deploy( Engine $engine )
	{
		$this->didError = false;
		$this->outputString = BaseAction::STRING_STATUS_SUCCESS;

		/* When deleting a node, we don't care what type it is or even whether
		   it exists... */
		try
		{
			/* ...so we don't try to infer or pass the type. */
			$targetNode = $engine->getFilesystemNodeFromPath( $this->getTargetPath() );

			/* If the node does exist, attempt to delete it. */
			$targetNode->delete();
		}

		/* If the node doesn't exist, we don't really care; silently ignore the
		   exception thrown by Fig::Engine */
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
