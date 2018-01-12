<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell;

use Fig\Shell\Shell;
use Fig\Action\Action;

abstract class ShellAction extends Action
{
	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Shell	$shell
	 *
	 * @return	void
	 */
	abstract public function deploy( Shell $shell );
}
