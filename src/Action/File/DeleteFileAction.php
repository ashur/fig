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
		try
		{
			$targetNode = $engine->getFilesystemNodeFromPath( $this->getTargetPath() );
			$targetNode->delete();
		}
		catch( NonExistentFilesystemPathException $e )
		{
            // Quietly ignore non-existent nodes during deletion
		}

		$this->didError = false;
		$this->outputString = BaseAction::STRING_STATUS_SUCCESS;
	}
}
