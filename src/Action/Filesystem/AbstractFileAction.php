<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Fig\Action\AbstractDeployableAction;
use Fig\Template;

abstract class AbstractFileAction extends AbstractDeployableAction
{
	use \Fig\Action\Filesystem\AbstractDeployWithFilesystemTrait;

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
		return Template::render( $this->targetPath, $this->vars );
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
