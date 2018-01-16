<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell;

use Fig\Shell;

trait DeployTrait
{
	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @return	void
	 */
	abstract public function deploy( Shell\Shell $shell );
}
