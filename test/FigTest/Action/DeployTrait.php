<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action;

use Fig\Action\AbstractAction;

trait DeployTrait
{
	abstract public function test_deploy_ignoringErrors( AbstractAction $action );
	abstract public function test_deploy_ignoringOutput( AbstractAction $action );
}
