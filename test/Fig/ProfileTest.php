<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use FigTest\TestCase;

class ProfileTest extends TestCase
{
	/* Helpers */

	public function createObject() : Profile
	{
		$name = getUniqueString( 'profile-' );

		$profile = new Profile( $name );
		return $profile;
	}

	public function createObject_fromName( string $name ) : Profile
	{
		$profile = new Profile( $name );
		return $profile;
	}


	/* Tests */

	public function test_addAction()
	{
		$profile = $this->createObject();

		$this->assertEquals( [], $profile->getActions() );

		$includedProfileName = getUniqueString( 'included-' );
		$action = new Action\Meta\IncludeAction( $includedProfileName );

		$profile->addAction( $action );

		$this->assertEquals( [$action], $profile->getActions() );
	}

	public function test_getActions_returnsArray()
	{
		$profile = $this->createObject();

		$this->assertEquals( [], $profile->getActions() );
	}

	public function test_getName()
	{
		$name = getUniqueString( 'profile-' );
		$profile = $this->createObject_fromName( $name );

		$this->assertEquals( $name, $profile->getName() );
	}

	public function test_usesVarsTrait()
	{
		$profile = $this->createObject();

		$this->assertTrue( method_exists( $profile, 'getVars' ) );
		$this->assertTrue( method_exists( $profile, 'setVars' ) );
	}
}
