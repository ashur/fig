<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell;

use Fig\Action;
use Fig\Shell;

trait AbstractDeployWithShellTrait
{
	/**
	 * Executes action and returns Result object
	 *
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @return	Fig\Action\Result
	 */
	abstract public function deployWithShell( Shell\Shell $shell ) : Action\Result;
}
