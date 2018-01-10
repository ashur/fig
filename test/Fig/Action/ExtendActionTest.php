<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use FigTest\Action\TestCase;

class ExtendActionTest extends TestCase
{
	public function test_getExtendedProfileName()
	{
		$actionName = sprintf( 'action %s', microtime( true ) );
		$profileName = sprintf( 'profile-%s', microtime( true ) );

		$action = new ExtendAction( $actionName, $profileName );

		$this->assertEquals( $profileName, $action->getExtendedProfileName() );
	}

	public function test_getName()
	{
		$actionName = sprintf( 'action %s', microtime( true ) );
		$profileName = sprintf( 'profile-%s', microtime( true ) );

		$action = new ExtendAction( $actionName, $profileName );

		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getSubtitle()
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new ExtendAction( $actionName, $profileName );

		$this->assertEquals( $profileName, $action->getSubtitle() );
	}

	public function test_getType()
	{
		$actionName = sprintf( 'action %s', microtime( true ) );
		$profileName = sprintf( 'profile-%s', microtime( true ) );

		$action = new ExtendAction( $actionName, $profileName );

		$this->assertEquals( 'Extend', $action->getType() );
	}
}
