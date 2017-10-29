<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
	/**
	 * Non-string values
	 *
	 * @return	array
	 */
	public function invalidStringProvider()
	{
		return [
			[ [] ],
			[ (object)[] ],
		];
	}

	/**
	 * Adding an Action to a Profile should set profileName and appName
	 */
	public function testAddingActionSetsProfileAndAppNames()
	{
		$profileName = 'foo-profile';
		$appName = 'bar-app';

		$profile = new Profile( $profileName, $appName );

		$action = new Action\Command
		([
			'name'		=> 'baz-action',
			'command'	=> 'echo hello'
		] );

		$profile->addAction( $action );
		$actions = $profile->getActions();

		$this->assertTrue( is_array( $actions ) );
		$this->assertEquals( 1, count( $actions ) );

		$this->assertEquals( $profileName, $actions[0]->getProfileName() );
		$this->assertEquals( $appName, $actions[0]->getAppName() );
	}

	public function testExtendWithUsesChildName()
	{
		$appName = 'app-' . time();

		/* Parent */
		$parentName = 'profile-parent';
		$parentProfile = new Profile( $parentName, $appName );

		/* Child */
		$childName = 'profile-child';
		$childProfile = new Profile( $childName, $appName );

		/* Extended */
		$extendedProfile = $parentProfile->extendWith( $childProfile );

		$this->assertEquals( $childName, $extendedProfile->getName() );
	}

	public function testExtendWithInheritsParentVariables()
	{
		$appName = 'app-' . time();

		/* Parent */
		$parentVariables['boo'] = 'far';
		$parentVariables['foo'] = 'bar';

		$parentProfile = new Profile( 'profile-parent', $appName );
		$parentProfile->setVariables( $parentVariables );

		/* Child */
		$childProfile = new Profile( 'profile-child', $appName );

		/* Extended */
		$extendedProfile = $parentProfile->extendWith( $childProfile );

		$this->assertEquals( $parentVariables, $extendedProfile->getVariables() );
	}

	public function testExtendWithChildAndParentVariables()
	{
		$appName = 'app-' . time();

		/* Parent */
		$parentVariables['foo'] = 'parent-foo';
		$parentVariables['bar'] = 'parent-bar';

		$parentProfile = new Profile( 'profile-parent', $appName );
		$parentProfile->setVariables( $parentVariables );

		/* Child */
		$childVariables['foo'] = 'child-foo';

		$childProfile = new Profile( 'profile-child', $appName );
		$childProfile->setVariables( $childVariables );

		/* Extended */
		$extendedVariables['foo'] = $childVariables['foo'];
		$extendedVariables['bar'] = $parentVariables['bar'];

		$extendedProfile = $parentProfile->extendWith( $childProfile );

		$this->assertEquals( $extendedVariables, $extendedProfile->getVariables() );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testGetInstanceFromFileWithNonArrayAction()
	{
		$appDirectory = new \Cranberry\Core\File\Directory( 'app' );

		/*
		 * Define File object stub
		 */
		$profileFileStub = $this->createMock( \Cranberry\Core\File\File::class );
		$profileFileStub
			->method( 'parent' )
			->willReturn( $appDirectory );

		$profileFileStub
			->method( 'getBasename' )
			->willReturn( 'profile' );

		$profileContents = <<<PROFILE
# invalid-yaml
---

This line is invalid YAML

- name: hello
  command: echo "hello, world"
PROFILE;

		$profileFileStub
			->method( 'getContents' )
			->willReturn( $profileContents );

		$profile = Profile::getInstanceFromFile( $profileFileStub );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testIncludingSelfThrowsException()
	{
		$profileName = 'foo-profile-' . time();
		$profile = new Profile ( $profileName, 'foo-app' );

		$action = new Action\Profile([
			'name'	=> 'test include action',
			'include'	=> $profileName,
		]);

		$profile->addAction( $action );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidName( $name )
	{
		$appName = 'foo-app-' . time();
		$profile = new Profile( $name, $appName );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidAppName( $appName )
	{
		$name = 'foo-profile-' . time();
		$profile = new Profile( $name, $appName );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 */
	public function testSetInvalidVariableValue( $variable )
	{
		$profile = new Profile( 'profile', 'app' );

		$variables['foo'] = $variable;
		$profile->setVariables( $variables );

		$this->assertEquals( [], $profile->getVariables() );
	}

	/**
	 * @dataProvider		validVariableValueProvider
	 */
	public function testSetValidVariableValue( $variable )
	{
		$profile = new Profile( 'profile', 'app' );

		$variables['foo'] = $variable;
		$profile->setVariables( $variables );

		$this->assertEquals( $variables, $profile->getVariables() );
	}

	/**
	 * Scalar values
	 *
	 * @return	array
	 */
	public function validVariableValueProvider()
	{
		return [
			[ 'foo' ],
			[ time() ],
			[ true ],
			[ false ],
		];
	}
}
