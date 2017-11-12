<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Fig\Action\BaseAction;

abstract class BaseFileAction extends BaseAction
{
	const ERROR_STRING_INVALIDTARGET = 'Invalid target %s: %s.';
	const ERROR_STRING_UNDELETABLE_NODE = 'Cannot delete %s: %s.';
	const ERROR_STRING_PERMISSION_DENIED = 'Permission denied';

	/**
	 * @var	string
	 */
	protected $subtitle;

	/**
	 * @var	string
	 */
	protected $targetPath;

	/**
	 * @var	string
	 */
	protected $type = 'File';

	/**
	 * Returns target file path
	 *
	 * @return	string
	 */
	public function getTargetPath() : string
	{
		return $this->replaceVariablesInString( $this->targetPath );
	}

	/**
	 * Returns action subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return $this->subtitle;
	}
}
