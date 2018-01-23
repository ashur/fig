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
		$includedProfileName = getUniqueString( 'profile-' );

		$result = new IncludeAction( $includedProfileName );
		return $result;
	}

	public function createObject_fromIncludedProfileName( string $includedProfileName ) : AbstractAction
	{
		$result = new IncludeAction( $includedProfileName );
		return $result;
	}

	public function createObject_fromArguments( array $arguments ) : AbstractAction
	{
		$includedProfileName = getUniqueString( 'profile-' );

		$result = new IncludeAction( $includedProfileName, $arguments );
		return $result;
	}

	/* Providers */

	public function provider_ActionObject() : array
	{
		return [
			[$this->createObject()]
		];
	}


	/* Tests */

	public function test_getArguments()
	{
		$arguments = [ 'foo' => getUniqueString( 'foo-' ), 'bar' => getUniqueString( 'bar-' ) ];

		$action = $this->createObject_fromArguments( $arguments );

		$this->assertEquals( $arguments, $action->getArguments() );
	}

	public function test_getIncludedProfileName()
	{
		$includedProfileName = getUniqueString( 'profile-' );

		$action = $this->createObject_fromIncludedProfileName( $includedProfileName );

		$this->assertEquals( $includedProfileName, $action->getIncludedProfileName() );
	}
}
