<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Fig\Action\BaseAction;

abstract class BaseFileAction extends BaseAction
{
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
