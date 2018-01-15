<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Meta;

use FigTest\Action\TestCase;

class ExtendActionTest extends TestCase
{
	/* Consumed by FigTest\Action\TestCase::test_getType */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new ExtendAction( $actionName, $profileName );

		return [
			[$action]
		];
	}

	public function test_getExtendedProfileName()
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new ExtendAction( $actionName, $profileName );

		$this->assertEquals( $profileName, $action->getExtendedProfileName() );
	}

	public function test_getName()
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

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
}
