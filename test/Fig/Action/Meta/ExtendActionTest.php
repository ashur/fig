<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Meta;

use Fig\Action\AbstractAction;
use FigTest\Action\ActionTestCase as TestCase;

class ExtendActionTest extends TestCase
{
	/* Helpers */

	public function createObject() : AbstractAction
	{
		$extendedProfileName = getUniqueString( 'profile-' );

		$result = new ExtendAction( $extendedProfileName );
		return $result;
	}

	public function createObject_fromExtendedProfileName( string $extendedProfileName ) : AbstractAction
	{
		$result = new ExtendAction( $extendedProfileName );
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

		$action = $this->createObject_fromExtendedProfileName( $extendedProfileName );

		$this->assertEquals( $extendedProfileName, $action->getExtendedProfileName() );
	}
}
