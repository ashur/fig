<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Fig\Action;
use Fig\Filesystem;

trait AbstractDeployWithFilesystemTrait
{
	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Filesystem\Filesystem	$filesystem
	 *
	 * @return	void
	 */
	abstract public function deployWithFilesystem( Filesystem\Filesystem $filesystem ) : Action\Result;
}