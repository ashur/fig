<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Fig\Action\BaseAction;
use Fig\Engine;
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
	 * @return	void
	 */
	public function deploy( Engine $engine )
	{
		/* When deleting a node, we don't care what type it is or even whether
		   it exists... */
		try
		{
			/* ...so we don't try to infer or pass the type... */
			$targetNode = $engine->getFilesystemNodeFromPath( $this->getTargetPath() );
			$targetNode->delete();
		}
		catch( NonExistentFilesystemPathException $e )
		{
			/* ...and we just silently ignore the exception thrown by trying to
			   instantiate a non-existent node object without a type. */
		}

		$this->didError = false;
		$this->outputString = BaseAction::STRING_STATUS_SUCCESS;
	}
}
