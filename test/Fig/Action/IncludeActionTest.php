<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use FigTest\Action\TestCase;

class IncludeActionTest extends TestCase
{
	/* Consumed by FigTest\Action\TestCase::test_getType */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new IncludeAction( $actionName, $profileName );

		return [
			[$action]
		];
	}

	public function test_getArguments()
	{
		$actionName = sprintf( 'action %s', microtime( true ) );
		$profileName = sprintf( 'profile-%s', microtime( true ) );
		$arguments = [ 'foo' => microtime( true ), 'bar' => microtime( true ) ];

		$action = new IncludeAction( $actionName, $profileName, $arguments );

		$this->assertEquals( $arguments, $action->getArguments() );
	}

	public function test_getIncludedProfileName()
	{
		$actionName = sprintf( 'action %s', microtime( true ) );
		$profileName = sprintf( 'profile-%s', microtime( true ) );

		$action = new IncludeAction( $actionName, $profileName );

		$this->assertEquals( $profileName, $action->getIncludedProfileName() );
	}

	public function test_getName()
	{
		$actionName = sprintf( 'action %s', microtime( true ) );
		$profileName = sprintf( 'profile-%s', microtime( true ) );

		$action = new IncludeAction( $actionName, $profileName );

		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getSubtitle()
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new IncludeAction( $actionName, $profileName );

		$this->assertEquals( $profileName, $action->getSubtitle() );
	}
}
