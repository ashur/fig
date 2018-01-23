<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Action\AbstractAction;
use FigTest\TestCase;

class AbstractActionTest extends TestCase
{
	/* Helpers */

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$actionMock = $this->getMockForAbstractClass( AbstractAction::class );
		return $actionMock;
	}


	/* Providers */

	


	/* Tests */

	public function test_isDeprecated_returnsFalseByDefault()
	{
		$action = $this->createObject_fromName( 'my abstract action ' );

		$this->assertFalse( $action->isDeprecated() );
	}
}
