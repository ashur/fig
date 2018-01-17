<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Fig\Filesystem;

trait DeployTrait
{
	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Filesystem\Filesystem	$filesystem
	 *
	 * @return	void
	 */
	abstract public function deploy( Filesystem\Filesystem $filesystem );
}
