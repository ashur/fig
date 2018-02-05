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

	public function provider_profileAncestors() : array
	{
		return [
			[
				[ getUniqueString( 'origin-' ), getUniqueString( 'generation-2-' ), getUniqueString( 'generation-3-' ), ]
			],
			[
				[ getUniqueString( 'origin-' ), getUniqueString( 'generation-2-' ) ]
			],
			[
				[ getUniqueString( 'origin-' ) ]
			],
		];
	}


	/* Tests */

	/**
	 * @dataProvider	provider_profileAncestors
	 */
	public function test_getProfileAncestry( array $profileAncestors  )
	{
		$action = $this->createObject();

		foreach( $profileAncestors as $profileName )
		{
			$action->setProfileName( $profileName );
		}

		/* Profile ancestry is stored in reverse chronological order */
		krsort( $profileAncestors );

		$this->assertEquals( array_values( $profileAncestors ), $action->getProfileAncestry() );
	}

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
