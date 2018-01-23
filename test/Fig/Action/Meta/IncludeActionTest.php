<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Meta;

use Fig\Action\AbstractAction;
use FigTest\Action\ActionTestCase as TestCase;

class IncludeActionTest extends TestCase
{
	/* Helpers */

	public function createObject() : AbstractAction
	{
		$name = getUniqueString( 'my include action ' );
		$includedProfileName = getUniqueString( 'profile-' );

		$result = new IncludeAction( $name, $includedProfileName );
		return $result;
	}

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$includedProfileName = getUniqueString( 'profile-' );

		$result = new IncludeAction( $name, $includedProfileName );
		return $result;
	}


	/* Providers */

	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new IncludeAction( $actionName, $profileName );

		return [
			[$action]
		];
	}


	/* Tests */

	public function test_getArguments()
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );
		$arguments = [ 'foo' => getUniqueString( 'foo-' ), 'bar' => getUniqueString( 'bar-' ) ];

		$action = new IncludeAction( $actionName, $profileName, $arguments );

		$this->assertEquals( $arguments, $action->getArguments() );
	}

	public function test_getIncludedProfileName()
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new IncludeAction( $actionName, $profileName );

		$this->assertEquals( $profileName, $action->getIncludedProfileName() );
	}

	public function test_getSubtitle()
	{
		$actionName = getUniqueString( 'action ' );
		$profileName = getUniqueString( 'profile-' );

		$action = new IncludeAction( $actionName, $profileName );

		$this->assertEquals( $profileName, $action->getSubtitle() );
	}
}
