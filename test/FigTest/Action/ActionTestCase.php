<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action;

use Fig\Action\AbstractAction;

/**
 * Base test class for all Action classes
 */
abstract class ActionTestCase extends \FigTest\TestCase
{
	/* Helpers */

	abstract public function createObject_fromName( string $name ) : AbstractAction;


	/* Providers */

	abstract public function provider_ActionObject() : array;


	/* Tests */

	public function test_getProfileName()
	{
		$action = $this->createObject_fromName( 'my action object' );

		$profileName = getUniqueString( 'profile-' );
		$action->setProfileName( $profileName );

		$this->assertEquals( $profileName, $action->getProfileName() );
	}

	/**
	 * @expectedException	LogicException
	 */
	public function test_getProfileName_throwsException_whenUndefined()
	{
		$action = $this->createObject_fromName( 'my action object' );
		$action->getProfileName();
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_getType( AbstractAction $action )
	{
		$actionType = $action->getType();

		$this->assertTrue( is_string( $actionType ) );
		$this->assertTrue( strlen( $actionType ) > 0 );
	}

	public function test_hasProfileName()
	{
		$action = $this->createObject_fromName( 'my action object' );

		$this->assertFalse( $action->hasProfileName() );

		$action->setProfileName( 'profile-name' );

		$this->assertTrue( $action->hasProfileName() );
	}

	public function test_isDeployable()
	{
		$name = getUniqueString( 'my action ' );
		$action = $this->createObject_fromName( $name );

		$this->assertFalse( $action->isDeployable() );
	}
}
