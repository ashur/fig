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

	public function test_isDeployable()
	{
		$name = getUniqueString( 'my action ' );
		$action = $this->createObject_fromName( $name );

		$this->assertTrue( $action->isDeployable() );
	}
}
