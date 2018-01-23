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

	abstract public function createObject() : AbstractAction;


	/* Providers */

	abstract public function provider_ActionObject() : array;


	/* Tests */

	public function test_getProfileName()
	{
		$action = $this->createObject();

		$profileName = getUniqueString( 'profile-' );
		$action->setProfileName( $profileName );

		$this->assertEquals( $profileName, $action->getProfileName() );
	}

	/**
	 * @expectedException	LogicException
	 */
	public function test_getProfileName_throwsException_whenUndefined()
	{
		$action = $this->createObject();
		$action->getProfileName();
	}

	public function test_hasProfileName()
	{
		$action = $this->createObject();

		$this->assertFalse( $action->hasProfileName() );

		$action->setProfileName( 'profile-name' );

		$this->assertTrue( $action->hasProfileName() );
	}

	public function test_isDeployable()
	{
		$name = getUniqueString( 'my action ' );
		$action = $this->createObject();

		$this->assertFalse( $action->isDeployable() );
	}
}
