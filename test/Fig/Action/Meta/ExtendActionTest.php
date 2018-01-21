<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Meta;

use Fig\Action\AbstractAction;
use FigTest\Action\TestCase;

class ExtendActionTest extends TestCase
{
	/* Helpers */

	public function createObject() : AbstractAction
	{
		$name = getUniqueString( 'my extend action ' );
		$extendedProfileName = getUniqueString( 'profile-' );

		$result = new ExtendAction( $name, $extendedProfileName );
		return $result;
	}

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$extendedProfileName = getUniqueString( 'profile-' );

		$result = new ExtendAction( $name, $extendedProfileName );
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

	public function test_getExtendedProfileName()
	{
		$name = getUniqueString( 'action ' );
		$extendedProfileName = getUniqueString( 'profile-' );

		$action = new ExtendAction( $name, $extendedProfileName );

		$this->assertEquals( $extendedProfileName, $action->getExtendedProfileName() );
	}

	public function test_getSubtitle()
	{
		$name = getUniqueString( 'action ' );
		$extendedProfileName = getUniqueString( 'profile-' );

		$action = new ExtendAction( $name, $extendedProfileName );

		$this->assertEquals( $extendedProfileName, $action->getSubtitle() );
	}
}
