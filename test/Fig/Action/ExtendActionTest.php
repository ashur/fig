<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use PHPUnit\Framework\TestCase;

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
}
